<?php

namespace App\Models;

use App\Models\Application;
use App\Support\TrademarkOppositionWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class TrademarkOppositionCase extends Model
{
    protected $fillable = [
        'case_number',
        'user_id',
        'flow_type',
        'trademark_you_own',
        'trademark_to_oppose',
        'opposed_application_number',
        'conflict_reason',
        'opposed_applicant_name',
        'user_business_name',
        'applicant_name',
        'trademark_name',
        'application_number',
        'trademark_class',
        'mobile_number',
        'email',
        'notice_receipt_date',
        'counter_statement_deadline',
        'third_party_status',
        'deadline_status',
        'current_admin_status',
        'current_client_stage',
        'risk_level',
        'risk_note',
        'recommendation_level',
        'recommendation_note',
        'recommendation_note_visible',
        'payment_status',
        'payment_reference',
        'transaction_id',
        'paid_at',
        'package_name',
        'package_price',
        'original_package_price',
        'paid_amount',
        'discount_amount',
        'coupon_label',
        'total_amount',
        'included_services',
        'add_ons',
        'client_approval_status',
        'client_approval_note',
        'client_change_request',
        'draft_client_note',
        'draft_path',
        'draft_name',
        'filing_acknowledgment_path',
        'filing_acknowledgment_name',
        'counter_statement_path',
        'counter_statement_name',
        'hearing_date',
        'final_outcome',
        'final_client_message',
        'withdrawn_by',
        'defence_case_status',
        'oppose_case_status',
        'admin_internal_notes',
        'requested_evidence_types',
        'evidence_request_note',
        'third_party_evidence_requests',
        'third_party_evidence_message',
        'third_party_evidence_pending',
        'first_use_date',
    ];

    protected $casts = [
        'notice_receipt_date' => 'date',
        'counter_statement_deadline' => 'date',
        'hearing_date' => 'date',
        'first_use_date' => 'date',
        'paid_at' => 'datetime',
        'package_price' => 'decimal:2',
        'original_package_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'included_services' => 'array',
        'add_ons' => 'array',
        'requested_evidence_types' => 'array',
        'third_party_evidence_requests' => 'array',
        'third_party_evidence_pending' => 'boolean',
        'recommendation_note_visible' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OppositionDocument::class, 'case_id');
    }

    public function grounds(): HasMany
    {
        return $this->hasMany(OppositionGround::class, 'case_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(OppositionEvidence::class, 'case_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CaseStatusHistory::class, 'case_id')->latest();
    }

    public function legalReviewPoints(): HasMany
    {
        return $this->hasMany(LegalReviewPoint::class, 'case_id');
    }

    public function draftDocuments(): HasMany
    {
        return $this->hasMany(DraftDocument::class, 'case_id')->latest();
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'case_id')->latest();
    }

    public function opposedApplication(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'opposed_application_number', 'application_number');
    }

    public function registryUpdates(): HasMany
    {
        return $this->hasMany(OppositionRegistryUpdate::class, 'case_id')
            ->orderByDesc('update_date')
            ->orderByDesc('id');
    }

    public function getNoticeFiledAtAttribute(): ?Carbon
    {
        $history = $this->statusHistories()
            ->where('new_status', TrademarkOppositionWorkflow::ADMIN_NOTICE_FILED)
            ->latest('created_at')
            ->first();

        return $history?->created_at;
    }

    public function hasRequiredDocuments(): bool
    {
        $uploaded = $this->documents()
            ->where('is_required', true)
            ->latest('id')
            ->get()
            ->unique('document_type')
            ->reject(fn (OppositionDocument $document) => $document->review_status === 'rejected')
            ->pluck('document_type')
            ->all();

        return empty(array_diff(array_keys(TrademarkOppositionWorkflow::requiredDocuments()), $uploaded));
    }

    public function getDeadlineStatusLabelAttribute(): string
    {
        return match ($this->deadline_status) {
            'green' => '30+ days left',
            'yellow' => '15-29 days left',
            'red' => 'Less than 15 days left',
            default => ucfirst((string) $this->deadline_status),
        };
    }

    public function getDraftDisplayNameAttribute(): string
    {
        $extension = strtolower((string) pathinfo((string) $this->draft_name, PATHINFO_EXTENSION));
        $baseName = $this->flow_type === TrademarkOppositionWorkflow::FLOW_OPPOSE
            ? 'Notice of Opposition Draft'
            : 'Counter Statement Draft';

        return $extension !== '' ? $baseName . '.' . $extension : $baseName;
    }
}
