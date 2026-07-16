<?php

namespace App\Services;

use App\Mail\EventNotification;
use App\Models\Application;
use App\Models\ApplicationStatusLog;
use App\Models\ApplicationTask;
use App\Models\Admin;
use App\Models\Document;
use App\Models\DraftVersion;
use App\Models\Notification;
use App\Support\TrademarkWorkflow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TrademarkWorkflowService
{
    public function initialize(Application $application): void
    {
        if (!$this->currentWorkflowStatus($application)) {
            $this->transition($application, TrademarkWorkflow::DRAFT, 'Application created.');
        }

        if ($this->hasApplicationsColumn('registry_status') && !$application->registry_status) {
            $application->forceFill([
                'registry_status' => TrademarkWorkflow::REGISTRY_NOT_FILED,
            ])->save();
        }
    }

    public function markAdvancePaymentComplete(Application $application): void
    {
        $this->transition($application, TrademarkWorkflow::APPLICATION_SUBMITTED, 'Advance payment completed.');
        $this->transition($application, TrademarkWorkflow::UNDER_REVIEW, 'Advance payment completed and application submitted for admin review.');
        $this->notifyUser($application, 'under_review', 'Application under review', 'Your application and advance payment have been received. Our team is reviewing your application details.');
        $this->notifyAdmins(
            'Applicant completed advance payment and submitted application',
            'The applicant completed the advance payment for application #' . $application->id . ' (' . ($application->brand_name ?: 'Trademark') . '). The application is ready for admin review.'
        );
    }

    public function submitForReview(Application $application): void
    {
        $this->transition($application, TrademarkWorkflow::UNDER_REVIEW, 'Application submitted for admin review.');
        $this->notifyUser($application, 'under_review', 'Application under review', 'Our team is reviewing your application details.');
        $this->notifyAdmins(
            'Applicant submitted application for review',
            'The applicant submitted application #' . $application->id . ' (' . ($application->brand_name ?: 'Trademark') . ') for admin review.'
        );
    }

    public function rejectReview(Application $application, ?string $reason = null): void
    {
        $this->transition($application, TrademarkWorkflow::REJECTED, $reason ?: 'Application rejected.');
        $application->forceFill(['rejection_reason' => $reason])->save();
        $this->notifyUser($application, 'application_rejected', 'Application rejected', $reason ?: 'Please review the rejection note.');
    }

    public function requestReviewChanges(Application $application, ?string $reason = null): void
    {
        $updates = ['rejection_reason' => $reason];

        if ($this->hasApplicationsColumn('admin_review_note')) {
            $updates['admin_review_note'] = $reason;
        }

        $application->forceFill($updates)->save();
        $this->transition($application, TrademarkWorkflow::APPLICATION_SUBMITTED, $reason ?: 'Admin requested application changes.');
        $this->notifyUser(
            $application,
            'application_changes_requested',
            'Application changes requested',
            $reason ?: 'Please recheck your trademark application details, make the requested changes, and submit it again for review.'
        );
    }

    public function approveReview(Application $application, ?string $note = null): void
    {
        $updates = [];

        if ($this->hasApplicationsColumn('workflow_meta')) {
            $meta = $application->workflow_meta ?? [];
            $meta['approval_note'] = $note;
            $updates['workflow_meta'] = $meta;
        }

        if ($this->hasApplicationsColumn('approved_at')) {
            $updates['approved_at'] = now();
        }

        if ($updates !== []) {
            $application->forceFill($updates)->save();
        }

        $this->ensureOnboardingPackage($application);
        $this->transition($application, TrademarkWorkflow::ONBOARDING_PENDING, $note ?: 'Admin approved application and issued onboarding package.');
        $this->notifyUser($application, 'onboarding_ready', 'Onboarding package ready', 'Please review the onboarding documents, electronically sign the Engagement Letter, and upload physically signed POA and Affidavit copies.');
    }

    public function refreshOnboardingStatus(Application $application): void
    {
        if ($application->current_status !== TrademarkWorkflow::ONBOARDING_PENDING) {
            return;
        }

        if (!$this->onboardingPackageReadyForStrategy($application)) {
            return;
        }

        $this->completeTask($application, 'engagement_letter_signed');
        $this->completeTask($application, 'poa_signed');
        $this->completeTask($application, 'signature_submitted');

        $this->saveApplicationFields($application, [
            'onboarding_completed_at' => now(),
        ]);
        $this->transition($application, TrademarkWorkflow::ONBOARDING_COMPLETED, 'User completed onboarding package.');
        $this->transition($application, TrademarkWorkflow::STRATEGY_IN_PROGRESS, 'Signed engagement letter, POA, and Affidavit verified. Strategy work started.');
        $this->ensureTask($application, 'strategy_report', 'Prepare strategy report', 'admin', 'strategy');
        $this->notifyUser($application, 'strategy_in_progress', 'Strategy work started', 'Your signed engagement letter, POA, and Affidavit have been verified. Our team is now preparing the trademark strategy and draft.');
    }

    private function onboardingPackageReadyForStrategy(Application $application): bool
    {
        foreach ([
            'engagement_letter (Signed)',
            'poa (Signed)',
            'affidavit (Signed)',
        ] as $documentType) {
            $document = $application->documents()
                ->where('document_type', $documentType)
                ->latest('id')
                ->first();

            if (!$document) {
                return false;
            }

            if ($document->status !== 'verified' && $document->verified_at) {
                $document->update(['status' => 'verified']);
            }

            if ($document->status !== 'verified') {
                return false;
            }
        }

        return true;
    }

    public function verifyKyc(Application $application, ?string $note = null): void
    {
        $this->saveApplicationFields($application, [
            'kyc_verified_at' => now(),
        ]);
        $this->transition($application, TrademarkWorkflow::STRATEGY_IN_PROGRESS, $note ?: 'Trademark search and strategy work started.');
        $this->ensureTask($application, 'strategy_report', 'Prepare strategy report', 'admin', 'strategy');
        $this->notifyUser($application, 'strategy_in_progress', 'Strategy work started', 'Your documents have been verified. Strategy preparation is now in progress.');
    }

    public function completeStrategy(Application $application, array $data = []): void
    {
        $updates = [
            'strategy_completed_at' => now(),
            'classes' => $data['recommended_classes'] ?? $application->classes,
            'goods_services' => $data['goods_services'] ?? $application->goods_services,
        ];

        if ($this->hasApplicationsColumn('workflow_meta')) {
            $meta = $application->workflow_meta ?? [];
            $strategyMeta = array_filter([
                'search_summary' => $data['search_summary'] ?? null,
                'risk_level' => $data['risk_level'] ?? null,
                'recommended_classes' => $data['recommended_classes'] ?? null,
                'warning_flags' => $data['warning_flags'] ?? null,
            ], fn ($value) => $value !== null && $value !== '');
            $meta['strategy'] = array_merge($meta['strategy'] ?? [], $strategyMeta);
            $updates['workflow_meta'] = $meta;
        }

        $this->saveApplicationFields($application, $updates);

        $this->completeTask($application, 'strategy_report');
        $this->transition($application, TrademarkWorkflow::STRATEGY_COMPLETED, 'Strategy report completed.');
        $this->notifyUser($application, 'strategy_completed', 'Strategy phase completed', 'Your trademark strategy phase has been completed. Our team is now preparing your draft for review.');
    }

    public function publishDraft(Application $application, array $data = []): DraftVersion
    {
        if (!Schema::hasTable('draft_versions')) {
            throw new \RuntimeException('Draft versions table is not available in the current database schema.');
        }

        $draft = DraftVersion::create([
            'application_id' => $application->id,
            'version_no' => ((int) $application->draftVersions()->max('version_no')) + 1,
            'classes' => $data['classes'] ?? $application->classes,
            'goods_services' => $data['goods_services'] ?? $application->goods_services,
            'tm_a_draft_path' => $data['tm_a_draft_path'] ?? null,
            'mark_preview_path' => $data['mark_preview_path'] ?? $application->logo_path,
            'prepared_by' => Auth::guard('admin')->id(),
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->saveApplicationFields($application, [
            'draft_ready_at' => now(),
        ]);
        $this->transition($application, TrademarkWorkflow::DRAFT_READY, 'Draft prepared.');
        $this->transition($application, TrademarkWorkflow::AWAITING_APPROVAL, 'Draft shared with client for approval.');
        $this->ensureTask($application, 'draft_review', 'Review trademark draft and approve or request changes', 'user', 'approval');
        $message = 'Your trademark draft is ready. Please review the attached draft and either approve it or request changes.';
        $draftNote = trim((string) ($data['draft_note'] ?? ''));

        if ($draftNote !== '') {
            $message .= "\n\nAdmin note: " . $draftNote;
        }

        $this->notifyUser(
            $application,
            'draft_ready',
            'Draft ready for approval',
            $message,
            $this->mailAttachmentsForPublicFiles([
                [
                    'path' => $draft->tm_a_draft_path,
                    'name' => 'trademark-draft-' . $application->id . '-v' . $draft->version_no . '.pdf',
                ],
            ])
        );

        return $draft;
    }

    public function requestDraftChanges(Application $application, string $comments): void
    {
        $this->assertAwaitingApproval($application);

        $draft = $application->draftVersions()->latest('id')->first();
        if ($draft) {
            $draft->update([
                'client_decision' => 'changes_requested',
                'client_comments' => $comments,
                'status' => 'changes_requested',
            ]);
        }

        $this->transition($application, TrademarkWorkflow::CHANGES_REQUESTED, $comments);
        $this->notifyUser($application, 'changes_requested', 'Changes request submitted', 'Your change requests have been shared with our drafting team.');
        $this->notifyAdmins(
            'Draft reupload requested',
            'The applicant requested draft changes for application #' . $application->id . ' (' . ($application->brand_name ?: 'Trademark') . ").\n\nRequest:\n" . $comments
        );
    }

    public function approveDraft(Application $application, array $approvalData): void
    {
        $this->assertAwaitingApproval($application);

        $draft = $application->draftVersions()->latest('id')->first();
        if ($draft) {
            $draft->update([
                'client_decision' => 'approved',
                'client_comments' => $approvalData['approval_notes'] ?? null,
                'approved_at' => now(),
                'status' => 'approved',
            ]);
        }

        $this->saveApplicationFields($application, [
            'client_approved_at' => now(),
        ]);
        $this->completeTask($application, 'draft_review');
        $this->transition($application, TrademarkWorkflow::APPROVED_FOR_FILING, 'Client approved the draft for filing.');

        if ($this->hasCompletedFullServicePayment($application)) {
            $this->markFinalPaymentComplete($application);
        } else {
            $this->transition($application, TrademarkWorkflow::PAYMENT_PENDING_FINAL, 'Final balance requested.');
            $this->notifyUser($application, 'final_payment_due', 'Final payment requested', 'Your draft has been approved. Please complete the remaining 50% payment to proceed with filing.');
        }

        $this->notifyAdmins(
            'Applicant approved draft',
            'The applicant approved the draft for application #' . $application->id . ' (' . ($application->brand_name ?: 'Trademark') . ').'
        );
    }

    public function markFinalPaymentComplete(Application $application): void
    {
        $this->saveApplicationFields($application, [
            'final_payment_completed_at' => now(),
            'application_number' => $application->application_number ?: 'TM-' . now()->format('Y') . '-' . $application->id,
            'filed_at' => now(),
            'registry_status' => TrademarkWorkflow::REGISTRY_FILED,
        ]);
        $this->transition($application, TrademarkWorkflow::PAYMENT_COMPLETED, 'Final payment completed.');
        $this->transition($application, TrademarkWorkflow::FILED, 'Final payment completed and application moved to filed stage.');
        $this->notifyUser($application, 'application_filed', 'Trademark filed successfully', 'Your final payment has been received and your application is now in the filed stage.');
        $this->notifyAdmins(
            'Applicant completed final payment',
            'The applicant completed final payment for application #' . $application->id . ' (' . ($application->brand_name ?: 'Trademark') . ').'
        );
    }

    private function hasCompletedFullServicePayment(Application $application): bool
    {
        if (!Schema::hasTable('payments')) {
            return false;
        }

        return $application->payments()
            ->whereIn('status', ['completed', 'approved'])
            ->get()
            ->contains(function ($payment) {
                if (
                    Schema::hasColumn('payments', 'payment_type')
                    && strtolower((string) $payment->payment_type) === 'full'
                ) {
                    return true;
                }

                return (float) $payment->total_amount > 0
                    && (float) $payment->amount >= (float) $payment->total_amount;
            });
    }

    public function markFiled(Application $application, array $data = []): void
    {
        $filingNote = trim((string) ($data['filing_note'] ?? ''));
        $updates = [
            'application_number' => $data['application_number'] ?? $application->application_number ?: 'TM-' . now()->format('Y') . '-' . $application->id,
            'filed_at' => now(),
            'filing_receipt_path' => $data['filing_receipt_path'] ?? $application->filing_receipt_path,
            'registry_status' => TrademarkWorkflow::REGISTRY_FILED,
        ];

        if ($filingNote !== '' && $this->hasApplicationsColumn('workflow_meta')) {
            $meta = $application->workflow_meta ?? [];
            $meta['filing']['admin_note'] = $filingNote;
            $meta['filing']['admin_note_added_at'] = now()->toDateTimeString();
            $updates['workflow_meta'] = $meta;
        }

        $this->saveApplicationFields($application, $updates);

        $this->transition($application, TrademarkWorkflow::FILED, $filingNote !== '' ? $filingNote : 'Trademark application filed.');
        $this->transition($application, TrademarkWorkflow::POST_FILING, 'Post-filing care started.');
        $this->saveApplicationFields($application, [
            'post_filing_started_at' => now(),
        ]);

        $message = 'Your application has been filed. You can now track the application number and post-filing milestones from your dashboard.';

        if ($filingNote !== '') {
            $message .= "\n\nAdmin note: " . $filingNote;
        }

        $this->notifyUser($application, 'application_filed', 'Trademark filed successfully', $message);
    }

    public function completeFiledStage(Application $application, ?string $note = null, array $mailAttachments = []): void
    {
        $this->saveApplicationFields($application, [
            'post_filing_started_at' => now(),
        ]);

        $this->transition($application, TrademarkWorkflow::POST_FILING, $note ?: 'Filed stage completed and post-filing care started.');
        $message = 'Your filed trademark matter has moved to post-filing care. We will keep you updated on examination, publication, opposition, and registration milestones.';

        if (filled($note)) {
            $message .= "\n\nAdmin note: " . $note;
        }

        $this->notifyUser($application, 'post_filing_started', 'Post-filing care started', $message, $mailAttachments);
    }

    public function ensureOnboardingPackage(Application $application): void
    {
        $this->ensureTask($application, 'engagement_letter_signed', 'Electronically sign engagement letter', 'user', 'onboarding');
        $this->ensureTask($application, 'poa_signed', 'Upload physically signed power of attorney', 'user', 'onboarding');
        $this->ensureTask($application, 'signature_submitted', 'Submit signed onboarding package', 'user', 'onboarding');
        $this->ensureTask($application, 'strategy_report', 'Prepare strategy report', 'admin', 'strategy');
    }

    public function ensureTask(Application $application, string $taskCode, string $title, string $assigneeType = 'user', ?string $group = null): ApplicationTask
    {
        if (!Schema::hasTable('application_tasks')) {
            return new ApplicationTask([
                'application_id' => $application->id,
                'task_code' => $taskCode,
                'task_group' => $group,
                'title' => $title,
                'assignee_type' => $assigneeType,
                'status' => 'pending',
            ]);
        }

        return ApplicationTask::firstOrCreate(
            [
                'application_id' => $application->id,
                'task_code' => $taskCode,
            ],
            [
                'task_group' => $group,
                'title' => $title,
                'assignee_type' => $assigneeType,
                'status' => 'pending',
            ]
        );
    }

    public function completeTask(Application $application, string $taskCode): void
    {
        if (!Schema::hasTable('application_tasks')) {
            return;
        }

        $task = $application->tasks()->where('task_code', $taskCode)->first();
        if ($task && $task->status !== 'completed') {
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }

    public function ensureSystemDocument(Application $application, string $type, string $title): Document
    {
        $existing = $application->documents()->where('document_type', $type)->latest('id')->first();
        if ($existing) {
            return $existing;
        }

        $filename = strtolower(str_replace(' ', '-', $title)) . '-' . $application->id . '.html';
        $path = 'workflow/' . $application->id . '/' . $filename;

        Storage::disk('public')->put($path, view('trademark.partials.workflow-document', [
            'application' => $application,
            'title' => $title,
            'body' => $this->documentBody($application, $type),
        ])->render());

        return Document::create([
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'document_type' => $type,
            'file_path' => $path,
            'file_name' => $filename,
            'file_type' => 'html',
            'file_size' => Storage::disk('public')->size($path),
            'status' => 'generated',
            'verification_notes' => $title . ' generated by workflow.',
        ]);
    }

    public function storeSignatureDocument(
        Application $application,
        string $mode,
        ?string $digitalSignature,
        $signatureImage,
        ?string $notes,
        string $type = 'signature',
        string $status = 'uploaded'
    ): Document {
        $application->documents()->where('document_type', $type)->each(function (Document $doc) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $doc->delete();
        });

        if ($mode === 'digital' || $mode === 'type' || $mode === 'draw' || $mode === 'upload' || !$signatureImage) {
            $filename = $type . '-' . $application->id . '-' . now()->timestamp . '.html';
            $path = 'workflow/' . $application->id . '/' . $filename;
            $html = view('user.partials.signature-document', [
                'application' => $application,
                'signatureText' => trim((string) $digitalSignature),
                'signatureNotes' => $notes,
                'submittedAt' => now(),
                'user' => $application->user,
            ])->render();

            Storage::disk('public')->put($path, $html);
            $size = strlen($html);
            $fileType = 'html';
            $fileName = $filename;
        } else {
            $extension = $signatureImage->getClientOriginalExtension();
            $filename = $type . '-' . $application->id . '-' . now()->timestamp . '.' . $extension;
            $path = 'workflow/' . $application->id . '/' . $filename;
            Storage::disk('public')->put($path, file_get_contents($signatureImage));
            $size = $signatureImage->getSize();
            $fileType = $extension;
            $fileName = $filename;
        }

        return Document::create([
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'document_type' => $type,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $size,
            'status' => $status,
            'verification_notes' => $notes ?: 'Workflow signature submitted.',
        ]);
    }

    public function transition(Application $application, string $toStatus, ?string $reason = null, array $metadata = []): void
    {
        $fromStatus = $this->currentWorkflowStatus($application) ?: $application->status;
        $updates = [
            'status' => $toStatus,
        ];

        if ($this->hasApplicationsColumn('service_status')) {
            $updates['service_status'] = $toStatus;
        }

        if ($this->hasApplicationsColumn('current_stage_started_at')) {
            $updates['current_stage_started_at'] = now();
        }

        $application->forceFill($updates)->save();

        if (Schema::hasTable('application_status_logs')) {
            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'actor_type' => Auth::guard('admin')->check() ? 'admin' : (Auth::check() ? 'user' : 'system'),
                'actor_id' => Auth::guard('admin')->id() ?: Auth::id(),
                'reason' => $reason,
                'metadata' => $metadata ?: null,
            ]);
        }
    }

    private function notifyUser(Application $application, string $type, string $title, string $message, array $mailAttachments = []): void
    {
        $actionUrl = route('trademark.status', $application->id);

        if (Schema::hasTable('notifications')) {
            Notification::create([
                'user_id' => $application->user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => [
                    'application_id' => $application->id,
                    'service_status' => $this->currentWorkflowStatus($application),
                    'action_url' => $actionUrl,
                ],
            ]);
        }

        try {
            $notification = new EventNotification(
                $application->user,
                $title,
                $message,
                $mailAttachments,
                $actionUrl
            );

            if (config('queue.default') !== 'sync') {
                Mail::to($application->user->email)->queue($notification);
            } elseif (config('mail.default') !== 'smtp') {
                Mail::to($application->user->email)->send($notification);
            } else {
                Log::warning('Skipped synchronous workflow notification email to avoid request timeout.', [
                    'application_id' => $application->id,
                    'type' => $type,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Workflow notification email failed.', [
                'application_id' => $application->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function mailAttachmentsForPublicFiles(array $files): array
    {
        return collect($files)
            ->map(function (array $file) {
                $path = $file['path'] ?? null;

                if (!is_string($path) || $path === '' || !Storage::disk('public')->exists($path)) {
                    return null;
                }

                return [
                    'path' => Storage::disk('public')->path($path),
                    'name' => $file['name'] ?? basename($path),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function notifyAdmins(string $title, string $message): void
    {
        try {
            Admin::query()->pluck('email')->filter()->each(function (string $email) use ($title, $message) {
                Mail::raw($message, function ($mail) use ($email, $title) {
                    $mail->to($email)->subject($title);
                });
            });
        } catch (\Throwable $e) {
            // Keep workflow transitions resilient even if admin mail delivery is unavailable.
        }
    }

    private function documentBody(Application $application, string $type): string
    {
        return match ($type) {
            'engagement_letter' => 'This engagement letter confirms the scope of our trademark filing services for ' . ($application->brand_name ?: 'your mark') . '.',
            'invoice' => 'Invoice for the onboarding package and the initial service engagement.',
            'tm_intake_form' => 'This TM intake form captures the owner details, mark details, use claim, goods/services, and filing instructions required to proceed with onboarding.',
            'terms_of_business' => 'These terms govern the professional services, timelines, and responsibilities for your trademark matter.',
            'poa' => 'Power of Attorney authorizing us to act for your trademark filing and prosecution matter.',
            default => 'Workflow document generated for this trademark application.',
        };
    }

    private function assertAwaitingApproval(Application $application): void
    {
        if ($this->currentWorkflowStatus($application) !== TrademarkWorkflow::AWAITING_APPROVAL) {
            abort(422, 'Draft actions are only available while the application is awaiting client review.');
        }
    }

    private function currentWorkflowStatus(Application $application): ?string
    {
        if ($this->hasApplicationsColumn('service_status')) {
            return $application->service_status;
        }

        return $application->status;
    }

    private function hasApplicationsColumn(string $column): bool
    {
        return Schema::hasColumn('applications', $column);
    }

    private function saveApplicationFields(Application $application, array $fields): void
    {
        $filtered = [];

        foreach ($fields as $column => $value) {
            if ($this->hasApplicationsColumn($column)) {
                $filtered[$column] = $value;
            }
        }

        if ($filtered !== []) {
            $application->forceFill($filtered)->save();
        }
    }
}
