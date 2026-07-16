<?php

namespace App\Models;

use App\Support\ExaminationReportReplyWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExaminationReportReplyCase extends Model
{
    protected $fillable = [
        'user_id',
        'case_number',
        'applicant_name',
        'trademark_name',
        'application_number',
        'trademark_class',
        'application_filing_date',
        'current_status',
        'filed_through_legalbruz',
        'exam_report_receipt_date',
        'reply_deadline',
        'deadline_status',
        'current_admin_status',
        'current_client_stage',
        'case_status',
        'objection_types',
        'portal_label',
        'section_9_reasons',
        'section_11_note',
        'section_11_details',
        'formal_objection_reasons',
        'evidence_required',
        'evidence_intake',
        'risk_level',
        'risk_reason',
        'client_visible_risk_note',
        'recommendation_note',
        'package_type',
        'package_price',
        'original_package_price',
        'package_description',
        'included_services',
        'add_ons',
        'payment_status',
        'payment_reference',
        'transaction_id',
        'paid_at',
        'paid_amount',
        'discount_amount',
        'coupon_label',
        'draft_status',
        'client_approval_status',
        'reply_filing_date',
        'acknowledgment_file',
        'registry_update_type',
        'registry_update_date',
        'hearing_date',
        'hearing_time',
        'hearing_mode',
        'hearing_link_or_location',
        'hearing_package_price',
        'hearing_payment_status',
        'final_outcome',
        'final_note_to_client',
        'client_visible_note',
        'internal_client_note',
        'internal_tracking_note',
        'closed_at',
    ];

    protected $casts = [
        'application_filing_date' => 'date',
        'filed_through_legalbruz' => 'boolean',
        'exam_report_receipt_date' => 'date',
        'reply_deadline' => 'date',
        'objection_types' => 'array',
        'section_9_reasons' => 'array',
        'section_11_details' => 'array',
        'formal_objection_reasons' => 'array',
        'evidence_required' => 'boolean',
        'evidence_intake' => 'array',
        'package_price' => 'decimal:2',
        'original_package_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'included_services' => 'array',
        'add_ons' => 'array',
        'paid_at' => 'datetime',
        'reply_filing_date' => 'date',
        'registry_update_date' => 'date',
        'hearing_date' => 'date',
        'hearing_package_price' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ExaminationReplyDocument::class, 'case_id');
    }

    public function requestedDocuments(): HasMany
    {
        return $this->hasMany(ExaminationReplyRequestedDocument::class, 'case_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ExaminationReplyStatusHistory::class, 'case_id')->latest();
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(ExaminationReplyDraft::class, 'case_id')->latest();
    }

    public function stageDrafts(): HasMany
    {
        return $this->hasMany(ExaminationReplyStageDraft::class, 'case_id')->latest();
    }

    public function stageRequests(): HasMany
    {
        return $this->hasMany(ExaminationReplyStageRequest::class, 'case_id')->latest();
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(ExaminationReplyNotificationLog::class, 'case_id')->latest();
    }

    public function getDeadlineStatusLabelAttribute(): string
    {
        return match ($this->deadline_status) {
            'green' => '20+ days left',
            'yellow' => '10-19 days left',
            'red' => 'Less than 10 days left',
            default => ucfirst((string) $this->deadline_status),
        };
    }

    public function getCurrentClientStageAttribute(?string $value): string
    {
        return $value ?: ExaminationReportReplyWorkflow::clientStageForAdminStatus($this->current_admin_status);
    }
}
