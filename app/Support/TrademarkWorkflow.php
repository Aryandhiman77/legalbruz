<?php

namespace App\Support;

class TrademarkWorkflow
{
    public const DRAFT = 'DRAFT';
    public const APPLICATION_SUBMITTED = 'APPLICATION_SUBMITTED';
    public const UNDER_REVIEW = 'UNDER_REVIEW';
    public const REJECTED = 'REJECTED';
    public const ONBOARDING_PENDING = 'ONBOARDING_PENDING';
    public const ONBOARDING_COMPLETED = 'ONBOARDING_COMPLETED';
    public const KYC_PENDING = 'KYC_PENDING';
    public const KYC_VERIFIED = 'KYC_VERIFIED';
    public const STRATEGY_IN_PROGRESS = 'STRATEGY_IN_PROGRESS';
    public const STRATEGY_COMPLETED = 'STRATEGY_COMPLETED';
    public const DRAFT_READY = 'DRAFT_READY';
    public const AWAITING_APPROVAL = 'AWAITING_APPROVAL';
    public const CHANGES_REQUESTED = 'CHANGES_REQUESTED';
    public const APPROVED_FOR_FILING = 'APPROVED_FOR_FILING';
    public const PAYMENT_PENDING_FINAL = 'PAYMENT_PENDING_FINAL';
    public const PAYMENT_COMPLETED = 'PAYMENT_COMPLETED';
    public const FILED = 'FILED';
    public const POST_FILING = 'POST_FILING';

    public const REGISTRY_NOT_FILED = 'NOT_FILED';
    public const REGISTRY_FILED = 'FILED_WITH_REGISTRY';
    public const REGISTRY_EXAM_PENDING = 'EXAM_PENDING';
    public const REGISTRY_OBJECTED = 'OBJECTED';
    public const REGISTRY_ACCEPTED = 'ACCEPTED_AND_ADVERTISED';
    public const REGISTRY_OPPOSITION = 'OPPOSITION_WINDOW';
    public const REGISTRY_REGISTERED = 'REGISTERED';

    public static function labels(): array
    {
        return [
            self::DRAFT => 'Draft',
            self::APPLICATION_SUBMITTED => 'Application Submitted',
            self::UNDER_REVIEW => 'Under Review',
            self::REJECTED => 'Rejected',
            self::ONBOARDING_PENDING => 'Onboarding Pending',
            self::ONBOARDING_COMPLETED => 'Onboarding Completed',
            self::KYC_PENDING => 'KYC Pending',
            self::KYC_VERIFIED => 'KYC Verified',
            self::STRATEGY_IN_PROGRESS => 'Strategy In Progress',
            self::STRATEGY_COMPLETED => 'Draft Preparation',
            self::DRAFT_READY => 'Draft Ready',
            self::AWAITING_APPROVAL => 'Awaiting Approval',
            self::CHANGES_REQUESTED => 'Changes Requested',
            self::APPROVED_FOR_FILING => 'Approved For Filing',
            self::PAYMENT_PENDING_FINAL => 'Final Payment Pending',
            self::PAYMENT_COMPLETED => 'Payment Completed',
            self::FILED => 'Filed',
            self::POST_FILING => 'Post Filing',
        ];
    }

    public static function label(?string $status): string
    {
        return static::labels()[$status] ?? str_replace('_', ' ', (string) $status);
    }

    public static function timeline(): array
    {
        return [
            self::APPLICATION_SUBMITTED,
            self::UNDER_REVIEW,
            self::ONBOARDING_PENDING,
            self::STRATEGY_IN_PROGRESS,
            self::DRAFT_READY,
            self::AWAITING_APPROVAL,
            self::PAYMENT_PENDING_FINAL,
            self::FILED,
            self::POST_FILING,
        ];
    }
}
