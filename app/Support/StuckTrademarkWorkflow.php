<?php

namespace App\Support;

class StuckTrademarkWorkflow
{
    public const INTAKE_SUBMITTED = 'INTAKE_SUBMITTED';
    public const CLIENT_ONBOARDING = 'CLIENT_ONBOARDING';
    public const PROBLEM_IDENTIFIED = 'PROBLEM_IDENTIFIED';
    public const AWAITING_DOCUMENTS = 'AWAITING_DOCUMENTS';
    public const DOCUMENTS_UPLOADED = 'DOCUMENTS_UPLOADED';
    public const DOCUMENTS_UNDER_VERIFICATION = 'DOCUMENTS_UNDER_VERIFICATION';
    public const REUPLOAD_REQUIRED = 'REUPLOAD_REQUIRED';
    public const DOCUMENTS_VERIFIED = 'DOCUMENTS_VERIFIED';
    public const AUDIT_PENDING = 'AUDIT_PENDING';
    public const AUDIT_IN_PROGRESS = 'AUDIT_IN_PROGRESS';
    public const AUDIT_COMPLETED = 'AUDIT_COMPLETED';
    public const AUDIT_REPORT_REVIEW = 'AUDIT_REPORT_REVIEW';
    public const AUDIT_REPORT_REUPLOAD_REQUESTED = 'AUDIT_REPORT_REUPLOAD_REQUESTED';
    public const AWAITING_APPROVAL = 'AWAITING_APPROVAL';
    public const EXECUTION_PAYMENT_PENDING = 'EXECUTION_PAYMENT_PENDING';
    public const EXECUTION_ACTIVE = 'EXECUTION_ACTIVE';
    public const REGISTRY_FOLLOW_UP = 'REGISTRY_FOLLOW_UP';
    public const MONITORING = 'MONITORING';
    public const ADDITIONAL_ACTION_REQUIRED = 'ADDITIONAL_ACTION_REQUIRED';
    public const RESOLVED = 'RESOLVED';
    public const CLOSED = 'CLOSED';

    public static function labels(): array
    {
        return [
            self::INTAKE_SUBMITTED => 'Intake Submitted',
            self::CLIENT_ONBOARDING => 'Client Onboarding',
            self::PROBLEM_IDENTIFIED => 'Problem Identified',
            self::AWAITING_DOCUMENTS => 'Awaiting Documents',
            self::DOCUMENTS_UPLOADED => 'Documents Uploaded',
            self::DOCUMENTS_UNDER_VERIFICATION => 'Documents Under Verification',
            self::REUPLOAD_REQUIRED => 'Reupload Required',
            self::DOCUMENTS_VERIFIED => 'Documents Verified',
            self::AUDIT_PENDING => 'Audit Pending',
            self::AUDIT_IN_PROGRESS => 'Audit In Progress',
            self::AUDIT_COMPLETED => 'Audit Completed',
            self::AUDIT_REPORT_REVIEW => 'Client Approval',
            self::AUDIT_REPORT_REUPLOAD_REQUESTED => 'Audit Report Reupload Requested',
            self::AWAITING_APPROVAL => 'Awaiting Approval',
            self::EXECUTION_PAYMENT_PENDING => 'Execution Payment Pending',
            self::EXECUTION_ACTIVE => 'Execution Active',
            self::REGISTRY_FOLLOW_UP => 'Registry Follow-Up',
            self::MONITORING => 'Monitoring',
            self::ADDITIONAL_ACTION_REQUIRED => 'Additional Action Required',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        ];
    }

    public static function label(?string $status): string
    {
        return static::labels()[$status] ?? str_replace('_', ' ', (string) $status);
    }

    public static function effectiveStatus(
        ?string $status,
        mixed $executionCompletedAt = null,
        mixed $resolvedAt = null,
        mixed $closedAt = null,
    ): ?string {
        if ($closedAt) {
            return self::CLOSED;
        }

        if ($resolvedAt) {
            return self::RESOLVED;
        }

        if ($executionCompletedAt && !in_array($status, [self::MONITORING, self::RESOLVED, self::CLOSED], true)) {
            return self::MONITORING;
        }

        return $status;
    }

    public static function timeline(): array
    {
        return [
            self::INTAKE_SUBMITTED,
            self::CLIENT_ONBOARDING,
            self::PROBLEM_IDENTIFIED,
            self::AWAITING_DOCUMENTS,
            self::DOCUMENTS_UPLOADED,
            self::DOCUMENTS_VERIFIED,
            self::AUDIT_PENDING,
            self::AUDIT_IN_PROGRESS,
            self::AUDIT_COMPLETED,
            self::AUDIT_REPORT_REVIEW,
            self::AWAITING_APPROVAL,
            self::EXECUTION_ACTIVE,
            self::REGISTRY_FOLLOW_UP,
            self::MONITORING,
            self::RESOLVED,
        ];
    }
}
