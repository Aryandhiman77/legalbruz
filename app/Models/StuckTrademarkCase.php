<?php

namespace App\Models;

use App\Support\StuckTrademarkWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StuckTrademarkCase extends Model
{
    protected $fillable = [
        'case_number',
        'user_id',
        'assigned_admin_id',
        'assigned_expert_name',
        'applicant_name',
        'business_name',
        'email',
        'phone',
        'applicant_address',
        'trademark_name',
        'application_number',
        'trademark_class',
        'filing_date',
        'registry_status',
        'issue_types',
        'urgency',
        'prior_attorney_name',
        'prior_attorney_contact',
        'filing_channel',
        'received_notices',
        'replies_filed_earlier',
        'onboarding_issue_types',
        'previous_attorney_details',
        'notices_received',
        'hearing_notices_missed',
        'status_unchanged_since',
        'objection_or_hearing_notice_received',
        'previous_attorney_explained_delay',
        'correction_requirement_informed',
        'problem_summary',
        'status',
        'audit_payment_status',
        'audit_fee',
        'audit_original_fee',
        'audit_paid_amount',
        'audit_discount_amount',
        'audit_coupon_label',
        'audit_paid_at',
        'audit_payment_reference',
        'audit_transaction_id',
        'audit_report_path',
        'audit_report_name',
        'audit_report_client_note',
        'audit_report_approved_at',
        'audit_report_reupload_requested_at',
        'audit_summary',
        'risk_level',
        'execution_recommendation',
        'execution_payment_status',
        'execution_fee',
        'execution_original_fee',
        'execution_paid_amount',
        'execution_discount_amount',
        'execution_coupon_label',
        'execution_paid_at',
        'execution_scope',
        'execution_status',
        'execution_sub_stage',
        'execution_additional_action_required',
        'execution_additional_action_name',
        'execution_additional_action_description',
        'execution_started_at',
        'execution_completed_at',
        'current_stage',
        'admin_note',
        'current_trademark_status',
        'monitoring_status',
        'audit_recommendation',
        'next_follow_up_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'filing_date' => 'date',
        'issue_types' => 'array',
        'onboarding_issue_types' => 'array',
        'audit_fee' => 'decimal:2',
        'audit_original_fee' => 'decimal:2',
        'audit_paid_amount' => 'decimal:2',
        'audit_discount_amount' => 'decimal:2',
        'audit_paid_at' => 'datetime',
        'audit_report_approved_at' => 'datetime',
        'audit_report_reupload_requested_at' => 'datetime',
        'execution_fee' => 'decimal:2',
        'execution_original_fee' => 'decimal:2',
        'execution_paid_amount' => 'decimal:2',
        'execution_discount_amount' => 'decimal:2',
        'execution_paid_at' => 'datetime',
        'execution_additional_action_required' => 'boolean',
        'execution_started_at' => 'datetime',
        'execution_completed_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StuckTrademarkDocument::class, 'case_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(StuckTrademarkStatusLog::class, 'case_id')->latest();
    }

    public function executionActions(): HasMany
    {
        return $this->hasMany(TrademarkExecutionAction::class, 'case_id');
    }

    public function executionUpdates(): HasMany
    {
        return $this->hasMany(TrademarkExecutionUpdate::class, 'case_id')->latest();
    }

    public function executionDocuments(): HasMany
    {
        return $this->hasMany(TrademarkExecutionDocument::class, 'case_id')->latest();
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(TrademarkDocumentRequest::class, 'case_id')->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return StuckTrademarkWorkflow::label($this->status);
    }

    public function getIssueSummaryAttribute(): string
    {
        return collect($this->issue_types ?? [])->map(fn ($issue) => ucwords(str_replace('_', ' ', $issue)))->implode(', ');
    }
}
