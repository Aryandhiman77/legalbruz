<?php

namespace App\Support;

use App\Models\ExaminationReportReplyCase;
use Illuminate\Support\Carbon;

class ExaminationReportReplyWorkflow
{
    public const ADMIN_APPLICATION_RECEIVED = 'Application Received';
    public const ADMIN_DOCUMENTS_PENDING = 'Documents Pending';
    public const ADMIN_REPORT_UNDER_REVIEW = 'Examination Report Under Review';
    public const ADMIN_OBJECTION_TYPE_IDENTIFIED = 'Objection Type Identified';
    public const ADMIN_EVIDENCE_REQUESTED = 'Evidence Requested';
    public const ADMIN_EVIDENCE_SUBMITTED = 'Evidence Submitted';
    public const ADMIN_EVIDENCE_REVIEW_COMPLETED = 'Evidence Review Completed';
    public const ADMIN_RISK_ASSESSMENT_COMPLETED = 'Risk Assessment Completed';
    public const ADMIN_PRICING_ASSIGNED = 'Pricing Assigned';
    public const ADMIN_PAYMENT_PENDING = 'Payment Pending';
    public const ADMIN_PAYMENT_COMPLETED = 'Payment Completed';
    public const ADMIN_REPLY_DRAFTING = 'Reply Drafting in Progress';
    public const ADMIN_DRAFT_UNDER_REVIEW = 'Draft Under Legal Review';
    public const ADMIN_CLIENT_APPROVAL_PENDING = 'Client Approval Pending';
    public const ADMIN_CHANGES_REQUESTED = 'Changes Requested by Client';
    public const ADMIN_READY_FOR_FILING = 'Ready for Filing';
    public const ADMIN_FILED_WITH_REGISTRY = 'Filed with Registry';
    public const ADMIN_ACKNOWLEDGMENT_UPLOADED = 'Acknowledgment Uploaded';
    public const ADMIN_AWAITING_REGISTRY_REVIEW = 'Awaiting Registry Review';
    public const ADMIN_ACCEPTED = 'Accepted';
    public const ADMIN_ACCEPTED_ADVERTISED = 'Accepted & Advertised';
    public const ADMIN_HEARING_ISSUED = 'Hearing Issued';
    public const ADMIN_FURTHER_ACTION_REQUIRED = 'Further Action Required';
    public const ADMIN_APPLICATION_ABANDONED = 'Application Abandoned';
    public const ADMIN_MATTER_CLOSED = 'Matter Closed';

    public const CLIENT_REQUEST_RECEIVED = 'Request Received';
    public const CLIENT_REPORT_REVIEW = 'Examination Report Review';
    public const CLIENT_EVIDENCE_COLLECTION = 'Evidence Collection';
    public const CLIENT_LEGAL_REVIEW_COMPLETED = 'Legal Review';
    public const CLIENT_PRICING_PAYMENT = 'Pricing & Payment';
    public const CLIENT_DRAFT_APPROVAL = 'Draft Approval';
    public const CLIENT_REPLY_FILED = 'Reply Filed';
    public const CLIENT_AWAITING_REGISTRY_UPDATE = 'Awaiting Registry Update';
    public const CLIENT_HEARING_REQUIRED = 'Hearing Required';
    public const CLIENT_MATTER_CLOSED = 'Matter Closed';

    public static function clientStageForAdminStatus(string $adminStatus): string
    {
        return match ($adminStatus) {
            self::ADMIN_APPLICATION_RECEIVED, self::ADMIN_DOCUMENTS_PENDING => self::CLIENT_REQUEST_RECEIVED,
            self::ADMIN_REPORT_UNDER_REVIEW, self::ADMIN_OBJECTION_TYPE_IDENTIFIED => self::CLIENT_REPORT_REVIEW,
            self::ADMIN_EVIDENCE_REQUESTED, self::ADMIN_EVIDENCE_SUBMITTED => self::CLIENT_EVIDENCE_COLLECTION,
            self::ADMIN_EVIDENCE_REVIEW_COMPLETED, self::ADMIN_RISK_ASSESSMENT_COMPLETED => self::CLIENT_LEGAL_REVIEW_COMPLETED,
            self::ADMIN_PRICING_ASSIGNED, self::ADMIN_PAYMENT_PENDING => self::CLIENT_PRICING_PAYMENT,
            self::ADMIN_PAYMENT_COMPLETED, self::ADMIN_REPLY_DRAFTING, self::ADMIN_DRAFT_UNDER_REVIEW, self::ADMIN_CLIENT_APPROVAL_PENDING,
            self::ADMIN_CHANGES_REQUESTED => self::CLIENT_DRAFT_APPROVAL,
            self::ADMIN_READY_FOR_FILING, self::ADMIN_FILED_WITH_REGISTRY => self::CLIENT_REPLY_FILED,
            self::ADMIN_ACKNOWLEDGMENT_UPLOADED, self::ADMIN_AWAITING_REGISTRY_REVIEW,
            self::ADMIN_FURTHER_ACTION_REQUIRED => self::CLIENT_AWAITING_REGISTRY_UPDATE,
            self::ADMIN_HEARING_ISSUED => self::CLIENT_HEARING_REQUIRED,
            self::ADMIN_ACCEPTED, self::ADMIN_ACCEPTED_ADVERTISED, self::ADMIN_APPLICATION_ABANDONED,
            self::ADMIN_MATTER_CLOSED => self::CLIENT_MATTER_CLOSED,
            default => self::CLIENT_REQUEST_RECEIVED,
        };
    }

    public static function getExaminationReplyClientStageFromAdminStatus(string $adminStatus): string
    {
        return self::clientStageForAdminStatus($adminStatus);
    }

    public static function clientTimeline(ExaminationReportReplyCase $case): array
    {
        return self::getExaminationReplyClientTimeline($case);
    }

    public static function getExaminationReplyClientTimeline(ExaminationReportReplyCase $case): array
    {
        $stageDefinitions = [
            self::CLIENT_REQUEST_RECEIVED => [
                'key' => 'request_received',
                'icon' => 'bi-file-earmark-check',
                'description' => 'We received your Trademark Objection Reply request. Our team will review your Examination Report and documents.',
            ],
            self::CLIENT_REPORT_REVIEW => [
                'key' => 'examination_report_review',
                'icon' => 'bi-search',
                'description' => 'Your Examination Report is being reviewed by our legal team.',
            ],
            self::CLIENT_EVIDENCE_COLLECTION => [
                'key' => 'evidence_collection',
                'icon' => 'bi-cloud-upload',
                'description' => 'Our legal team needs proof to prepare a stronger reply.',
            ],
            self::CLIENT_LEGAL_REVIEW_COMPLETED => [
                'key' => 'legal_review_completed',
                'icon' => 'bi-clipboard2-check',
                'description' => 'Your Examination Report and documents have been reviewed. Please check the risk level and next steps.',
            ],
            self::CLIENT_PRICING_PAYMENT => [
                'key' => 'pricing_payment',
                'icon' => 'bi-credit-card',
                'description' => 'Please review the selected package and complete payment to start reply drafting.',
            ],
            self::CLIENT_DRAFT_APPROVAL => [
                'key' => 'draft_approval',
                'icon' => 'bi-file-earmark-text',
                'description' => 'Your objection reply draft is ready for review.',
            ],
            self::CLIENT_REPLY_FILED => [
                'key' => 'reply_filed',
                'icon' => 'bi-send-check',
                'description' => 'Your reply to the Trademark Examination Report has been filed with the Trademark Registry.',
            ],
            self::CLIENT_AWAITING_REGISTRY_UPDATE => [
                'key' => 'awaiting_registry_update',
                'icon' => 'bi-hourglass-split',
                'description' => 'Your reply has been filed. The application is now waiting for review by the Trademark Registry.',
            ],
            self::CLIENT_HEARING_REQUIRED => [
                'key' => 'hearing_required',
                'icon' => 'bi-megaphone',
                'description' => 'The Trademark Registry has issued a hearing notice. Hearing support is separate from the objection reply service unless purchased separately.',
                'is_optional' => true,
            ],
            self::CLIENT_MATTER_CLOSED => [
                'key' => 'matter_closed',
                'icon' => 'bi-check2-circle',
                'description' => 'The matter has been closed with a final Registry update.',
            ],
        ];

        $documents = $case->relationLoaded('documents') ? $case->documents : $case->documents()->get();
        $histories = $case->relationLoaded('statusHistories') ? $case->statusHistories : $case->statusHistories()->get();
        $stageRequests = $case->relationLoaded('stageRequests') ? $case->stageRequests : $case->stageRequests()->get();
        $notificationLogs = $case->relationLoaded('notificationLogs') ? $case->notificationLogs : $case->notificationLogs()->get();
        $requestedDocuments = $case->relationLoaded('requestedDocuments')
            ? $case->requestedDocuments
            : $case->requestedDocuments()->with('stageRequest')->get();
        $requestedDocumentStageKeys = $requestedDocuments
            ->sortByDesc('id')
            ->groupBy(fn ($requested) => str_replace('-', '_', str($requested->document_name)->slug()->toString()))
            ->map(function ($requests) {
                return match ($requests->first()?->stageRequest?->to_stage) {
                    self::CLIENT_AWAITING_REGISTRY_UPDATE => 'awaiting_registry_update',
                    self::CLIENT_EVIDENCE_COLLECTION => 'evidence_collection',
                    default => null,
                };
            });
        $hasHearingHistory = $case->current_admin_status === self::ADMIN_HEARING_ISSUED
            || $case->final_outcome === 'Hearing Required'
            || $histories->contains(fn ($history) => $history->new_admin_status === self::ADMIN_HEARING_ISSUED
                || self::normalizeClientStage($history->new_client_stage) === self::CLIENT_HEARING_REQUIRED);

        if (! $hasHearingHistory) {
            unset($stageDefinitions[self::CLIENT_HEARING_REQUIRED]);
        }

        $stageKeyForLabel = collect($stageDefinitions)
            ->mapWithKeys(fn (array $definition, string $label) => [$label => $definition['key']]);
        $conversations = collect();
        $addConversation = function (?string $stageKey, string $sender, ?string $message, $createdAt) use ($conversations): void {
            $message = trim((string) $message);
            if (!$stageKey || $message === '') {
                return;
            }

            $signature = $stageKey . '|' . $sender . '|' . mb_strtolower(preg_replace('/\s+/', ' ', $message));
            if ($conversations->has($signature)) {
                return;
            }

            $conversations->put($signature, [
                'stage_key' => $stageKey,
                'sender' => $sender,
                'sender_label' => $sender === 'client' ? 'You' : 'Legal Team',
                'message' => $message,
                'created_at' => $createdAt,
            ]);
        };

        $stageRequests->sortBy('created_at')->each(function ($stageRequest) use ($addConversation, $stageKeyForLabel): void {
            $stageLabel = self::normalizeClientStage($stageRequest->to_stage);
            $addConversation($stageKeyForLabel->get($stageLabel), 'admin', $stageRequest->client_message, $stageRequest->created_at);
        });

        $notificationStageKeys = [
            'order_received' => 'request_received',
            'documents_pending' => 'request_received',
            'evidence_requested' => 'evidence_collection',
            'draft_ready' => 'draft_approval',
            'filed' => 'reply_filed',
            'awaiting_registry' => 'awaiting_registry_update',
            'hearing_issued' => 'hearing_required',
            'matter_closed' => 'matter_closed',
        ];
        $notificationLogs
            ->where('channel', 'in_app')
            ->sortBy('created_at')
            ->each(function ($log) use ($addConversation, $notificationStageKeys, $histories, $stageKeyForLabel): void {
                $stageKey = $notificationStageKeys[$log->notification_type] ?? null;
                if (!$stageKey) {
                    $nearestHistory = $histories
                        ->filter(fn ($history) => $history->created_at && $log->created_at)
                        ->sortBy(fn ($history) => abs($history->created_at->diffInSeconds($log->created_at, false)))
                        ->first();
                    if ($nearestHistory && abs($nearestHistory->created_at->diffInSeconds($log->created_at, false)) <= 10) {
                        $stageKey = $stageKeyForLabel->get(self::normalizeClientStage($nearestHistory->new_client_stage));
                    }
                }

                $addConversation($stageKey, 'admin', $log->message, $log->sent_at ?: $log->created_at);
            });

        $histories
            ->where('changed_by', 'client')
            ->sortBy('created_at')
            ->each(function ($history) use ($addConversation, $stageKeyForLabel): void {
                $message = trim((string) $history->note);
                if (preg_match('/Message to admin:\s*(.+)$/is', $message, $matches)) {
                    $message = trim($matches[1]);
                } elseif ($history->new_admin_status !== self::ADMIN_CHANGES_REQUESTED) {
                    return;
                }

                $stageKey = $stageKeyForLabel->get(self::normalizeClientStage($history->new_client_stage));
                $addConversation($stageKey, 'client', $message, $history->created_at);
            });

        $conversationsByStage = $conversations
            ->values()
            ->sortBy(fn (array $message) => $message['created_at']?->getTimestamp() ?? 0)
            ->groupBy('stage_key');

        $visibleStageLabels = array_keys($stageDefinitions);
        $currentClientStage = self::normalizeClientStage(self::clientStageForAdminStatus($case->current_admin_status));
        $currentIndex = collect($visibleStageLabels)->search($currentClientStage);
        $currentIndex = $currentIndex === false ? 0 : (int) $currentIndex;
        return collect($stageDefinitions)
            ->map(function (array $definition, string $stageLabel) use ($case, $documents, $histories, $requestedDocumentStageKeys, $conversationsByStage, $currentIndex, $visibleStageLabels) {
                $index = (int) array_search($stageLabel, $visibleStageLabels, true);
                $completedAt = $histories
                    ->filter(fn ($history) => self::normalizeClientStage($history->new_client_stage) === $stageLabel)
                    ->sortByDesc('id')
                    ->first()?->created_at;
                $stageDocuments = $documents
                    ->where('visibility', 'client')
                    ->filter(function ($document) use ($definition, $requestedDocumentStageKeys) {
                        $storedStageKey = trim((string) $document->stage_key);
                        $effectiveStageKey = match ($storedStageKey) {
                            'draft' => 'draft_approval',
                            'filing' => 'reply_filed',
                            'registry_update' => 'awaiting_registry_update',
                            'close' => 'matter_closed',
                            default => $storedStageKey,
                        };

                        if ($effectiveStageKey === '' && $document->uploaded_by === 'client') {
                            $effectiveStageKey = (string) ($requestedDocumentStageKeys->get($document->document_type) ?? '');
                        }

                        return $effectiveStageKey !== ''
                            ? $effectiveStageKey === $definition['key']
                            : self::documentBelongsToStage($document->document_type, $definition['key']);
                    })
                    ->values();
                $isClosedMatterComplete = $stageLabel === self::CLIENT_MATTER_CLOSED
                    && ($case->current_admin_status === self::ADMIN_MATTER_CLOSED
                        || $case->case_status === 'closed'
                        || $case->closed_at !== null);
                $status = $isClosedMatterComplete
                    ? 'completed'
                    : ($index < $currentIndex ? 'completed' : ($index === $currentIndex ? 'active' : 'pending'));
                $isDraftReuploadRequested = $stageLabel === self::CLIENT_DRAFT_APPROVAL
                    && $status === 'active'
                    && $case->current_admin_status === self::ADMIN_CHANGES_REQUESTED;
                $isApprovedAwaitingFiling = $stageLabel === self::CLIENT_REPLY_FILED
                    && $status === 'active'
                    && $case->current_admin_status === self::ADMIN_READY_FOR_FILING;

                return [
                    'stage_key' => $definition['key'],
                    'stage_label' => $stageLabel,
                    'stage' => $stageLabel,
                    'description' => match (true) {
                        $isDraftReuploadRequested => 'Changes have been requested for your objection reply draft. You will be notified when the revised draft is ready.',
                        $isApprovedAwaitingFiling => 'Your objection reply draft has been approved. Our team is preparing the reply for filing with the Trademark Registry.',
                        default => $definition['description'],
                    },
                    'icon' => $definition['icon'],
                    'status' => $status,
                    'status_label' => $isDraftReuploadRequested
                        ? 'Asked for Reupload'
                        : ($status === 'completed' ? 'Completed' : ($status === 'active' ? 'Active' : 'Pending')),
                    'completed_at' => in_array($status, ['completed', 'active'], true) ? $completedAt : null,
                    'documents_available' => $stageDocuments->isNotEmpty(),
                    'documents' => $stageDocuments,
                    'conversation_available' => $conversationsByStage->has($definition['key']),
                    'conversation' => $conversationsByStage->get($definition['key'], collect())->values(),
                    'is_optional' => (bool) ($definition['is_optional'] ?? false),
                    'should_show' => true,
                ];
            })
            ->values()
            ->all();
    }

    public static function stageRank(ExaminationReportReplyCase $case): int
    {
        $stages = collect(self::clientTimeline($case))->pluck('stage')->values();
        $index = $stages->search(self::normalizeClientStage($case->current_client_stage));

        return $index === false ? 0 : (int) $index;
    }

    public static function normalizeClientStage(?string $stage): string
    {
        return match ($stage) {
            'Legal Review Completed' => self::CLIENT_LEGAL_REVIEW_COMPLETED,
            default => $stage ?: self::CLIENT_REQUEST_RECEIVED,
        };
    }

    public static function documentBelongsToStage(string $documentType, string $stageKey): bool
    {
        return match ($stageKey) {
            'request_received' => in_array($documentType, ['examination_report', 'tm_a_acknowledgment', 'logo_device_mark'], true),
            'evidence_collection' => !in_array($documentType, [
                'examination_report',
                'tm_a_acknowledgment',
                'logo_device_mark',
                'reply_draft',
                'filing_acknowledgment',
                'registry_update',
                'hearing_notice',
                'final_registry_document',
            ], true),
            'draft_approval' => $documentType === 'reply_draft',
            'reply_filed' => $documentType === 'filing_acknowledgment',
            'awaiting_registry_update' => $documentType === 'registry_update',
            'hearing_required' => $documentType === 'hearing_notice',
            'matter_closed' => $documentType === 'final_registry_document',
            default => str_contains($documentType, $stageKey),
        };
    }

    public static function deadlineStatus(Carbon|string $deadline): string
    {
        $daysLeft = now()->startOfDay()->diffInDays(Carbon::parse($deadline)->startOfDay(), false);

        return match (true) {
            $daysLeft >= 20 => 'green',
            $daysLeft >= 10 => 'yellow',
            default => 'red',
        };
    }

    public static function evidenceDocumentTypes(): array
    {
        return [
            'invoice' => 'Invoice',
            'website_screenshot' => 'Website screenshot',
            'instagram_screenshot' => 'Instagram page screenshot',
            'marketplace_listing' => 'Amazon / Flipkart listing',
            'packaging_photo' => 'Packaging photo',
            'business_registration' => 'Business registration certificate',
            'advertisement_material' => 'Advertisement material',
            'gst_certificate' => 'GST certificate',
            'domain_invoice' => 'Domain invoice',
            'social_media_proof' => 'Social media proof',
            'sales_proof' => 'Sales proof',
            'user_affidavit' => 'User affidavit',
            'business_difference' => 'Explanation of business difference',
            'market_difference' => 'Market difference',
            'logo_word_difference' => 'Logo/word difference',
            'customer_segment_difference' => 'Customer segment difference',
            'goods_services_difference' => 'Goods/services difference',
        ];
    }

    public static function notificationMessage(string $type): string
    {
        return match ($type) {
            'order_received' => 'Your Trademark Objection Reply request has been received. Please ensure all documents are uploaded so our team can review the Examination Report.',
            'documents_pending' => 'Your objection reply is time-sensitive. Please upload the required documents as soon as possible to avoid delay in filing.',
            'evidence_requested' => 'Our legal team needs additional proof to prepare your objection reply. Please upload the requested documents from your dashboard.',
            'draft_ready' => 'Your trademark objection reply draft is ready for review. Please approve it so we can proceed with filing.',
            'filed' => 'Your reply to the Trademark Examination Report has been filed. The filing acknowledgment is now available in your dashboard.',
            'awaiting_registry' => 'Your reply has been filed and is now awaiting review by the Trademark Registry.',
            'hearing_issued' => 'The Trademark Registry has issued a hearing notice. This is a separate stage and requires hearing preparation and representation.',
            'matter_closed' => 'The Registry has issued its update. Please log in to view the final status and documents.',
            default => 'Your Trademark Objection Reply case has been updated.',
        };
    }
}
