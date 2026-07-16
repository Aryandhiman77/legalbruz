<?php

namespace App\Support;

use Carbon\CarbonInterface;

class TrademarkOppositionWorkflow
{
    public const FLOW_DEFEND = 'defend_my_trademark';
    public const FLOW_OPPOSE = 'oppose_a_trademark';

    public const ADMIN_APPLICATION_RECEIVED = 'Application Received';
    public const ADMIN_DOCUMENTS_PENDING = 'Documents Pending';
    public const ADMIN_OPPOSITION_REVIEW = 'Opposition Review';
    public const ADMIN_LEGAL_ANALYSIS = 'Legal Analysis';
    public const ADMIN_EVIDENCE_COLLECTION = 'Evidence Collection';
    public const ADMIN_COUNTER_STATEMENT_DRAFTING = 'Counter Statement Drafting';
    public const ADMIN_CLIENT_APPROVAL = 'Client Approval';
    public const ADMIN_READY_FOR_FILING = 'Ready for Filing';
    public const ADMIN_COUNTER_STATEMENT_FILED = 'Counter Statement Filed';
    public const ADMIN_AWAITING_EVIDENCE_STAGE = 'Awaiting Evidence Stage';
    public const ADMIN_EVIDENCE_FILED = 'Evidence Filed';
    public const ADMIN_HEARING_PREPARATION = 'Hearing Preparation';
    public const ADMIN_HEARING_COMPLETED = 'Hearing Completed';
    public const ADMIN_DECISION_AWAITED = 'Decision Awaited';
    public const ADMIN_FINAL_OUTCOME = 'Final Outcome';
    public const ADMIN_OPPOSITION_ALLOWED = 'Opposition Allowed';
    public const ADMIN_OPPOSITION_DISMISSED = 'Opposition Dismissed';
    public const ADMIN_SETTLEMENT_CLOSED = 'Settlement Closed';
    public const ADMIN_WITHDRAWN = 'Withdrawn';
    public const ADMIN_OTHER = 'Other';

    public const CLIENT_CASE_OPENED = 'Case Opened';
    public const CLIENT_UNDER_REVIEW = 'Under Review';
    public const CLIENT_LEGAL_REVIEW = 'Legal Review Underway';
    public const CLIENT_EVIDENCE_COLLECTION = 'Evidence Collection';
    public const CLIENT_PRICING_PAYMENT = 'Pricing & Payment';
    public const CLIENT_DRAFTING = 'Drafting in Progress';
    public const CLIENT_DOCUMENT_FILED = 'Document Filed';
    public const CLIENT_AWAITING_OTHER_PARTY = 'Awaiting Third Party Action';
    public const CLIENT_AWAITING_EVIDENCE_STAGE = 'Awaiting Evidence Stage';
    public const CLIENT_EVIDENCE_STAGE = 'Evidence Stage';
    public const CLIENT_HEARING_STAGE = 'Hearing Stage';
    public const CLIENT_DECISION_AWAITED = 'Decision Awaited';
    public const CLIENT_MATTER_CLOSED = 'Matter Closed';

    public const ADMIN_LEGAL_ANALYSIS_COMPLETED = 'Legal Analysis Completed';
    public const ADMIN_NOTICE_DRAFTING = 'Notice Drafting in Progress';
    public const ADMIN_DRAFT_UNDER_LEGAL_REVIEW = 'Draft Under Legal Review';
    public const ADMIN_CLIENT_APPROVAL_PENDING = 'Client Approval Pending';
    public const ADMIN_NOTICE_FILED = 'Notice of Opposition Filed';
    public const ADMIN_COUNTER_STATEMENT_AWAITED = 'Counter Statement Awaited';
    public const ADMIN_EVIDENCE_BY_OPPONENT = 'Evidence by Opponent';
    public const ADMIN_EVIDENCE_BY_APPLICANT = 'Evidence by Applicant';
    public const ADMIN_EVIDENCE_IN_REPLY = 'Evidence in Reply';
    public const ADMIN_HEARING_SCHEDULED = 'Hearing Scheduled';
    public const ADMIN_HEARING_ADJOURNED = 'Adjourned';
    public const ADMIN_MATTER_CLOSED = 'Matter Closed';

    public const THIRD_PARTY_AWAITING_RESPONSE = 'awaiting_response';
    public const THIRD_PARTY_COUNTER_STATEMENT_RECEIVED = 'counter_statement_received';
    public const THIRD_PARTY_NO_RESPONSE = 'no_response_received';
    public const THIRD_PARTY_DEADLINE_EXPIRED = 'deadline_expired';
    public const DEFENCE_CASE_SUCCEEDED = 'defence_case_succeeded';
    public const DEFENCE_CASE_FAILED_TO_SUCCEED = 'failed_to_succeed';
    public const DEFENCE_CASE_CLOSED = 'closed';
    public const OPPOSE_CASE_SUCCEEDED = 'oppose_case_succeeded';
    public const OPPOSE_CASE_FAILED_TO_SUCCEED = 'oppose_case_failed_to_succeed';
    public const OPPOSE_CASE_CLOSED = 'closed';
    public const WITHDRAWN_BY_OPPONENT = 'opponent';
    public const WITHDRAWN_BY_APPLICANT = 'applicant';
    public const FINAL_OUTCOME_WITHDRAWN_BY_OPPONENT = 'withdrawn_by_opponent';
    public const FINAL_OUTCOME_WITHDRAWN_BY_APPLICANT = 'withdrawn_by_applicant';
    public const FINAL_OUTCOME_OTHER = 'other';
    public const APPLICATION_OPPOSITION_SUCCEEDED = 'opposition_succeeded';
    public const APPLICATION_OPPOSITION_FAILED = 'opposition_failed';
    public const APPLICATION_SETTLEMENT_CLOSED = 'settlement_closed';
    public const APPLICATION_OPPOSITION_WITHDRAWN = 'opposition_withdrawn';
    public const APPLICATION_DEFENCE_SUCCEEDED = 'defence_succeeded';
    public const APPLICATION_DEFENCE_FAILED = 'defence_failed';
    public const APPLICATION_FINAL_RESULT_OPPOSITION_SUCCESSFUL = 'opposition_successful';
    public const APPLICATION_FINAL_RESULT_DEFENCE_SUCCESSFUL = 'defence_successful';
    public const APPLICATION_FINAL_RESULT_SETTLEMENT_CLOSED = 'settlement_closed';
    public const APPLICATION_FINAL_RESULT_OPPOSITION_WITHDRAWN = 'opposition_withdrawn';
    public const APPLICATION_FINAL_RESULT_APPLICATION_WITHDRAWN = 'application_withdrawn';
    public const APPLICATION_FINAL_RESULT_OTHER = 'other';

    public static function requiredDocuments(): array
    {
        return [
            'notice_of_opposition' => 'Notice of Opposition',
            'tm_application_acknowledgment' => 'TM Application Acknowledgment',
            'logo_or_trademark_copy' => 'Logo / Trademark Copy',
        ];
    }

    public static function optionalDocuments(): array
    {
        return [
            'trademark_journal_copy' => 'Trademark Journal Copy',
            'previous_registry_communication' => 'Any Previous Registry Communication',
        ];
    }

    public static function evidenceTypes(): array
    {
        return [
            'invoices' => 'Invoices',
            'gst_certificate' => 'GST Certificate',
            'packaging' => 'Packaging',
            'website_screenshots' => 'Website Screenshots',
            'domain_registration' => 'Domain Registration',
            'amazon_listings' => 'Amazon Listings',
            'flipkart_listings' => 'Flipkart Listings',
            'advertising_material' => 'Advertising Material',
            'social_media_evidence' => 'Social Media Evidence',
            'client_purchase_orders' => 'Client Purchase Orders',
            'catalogue' => 'Catalogue',
            'photos_of_product' => 'Photos of Product',
            'sales_figures' => 'Sales Figures',
            'marketing_spend_proof' => 'Marketing Spend Proof',
        ];
    }

    public static function opposeEvidenceTypes(): array
    {
        return [
            'registration_certificate' => 'Your Registration Certificate',
            'trademark_application' => 'Your Trademark Application',
            'use_proof' => 'Use Proof',
            'opposed_trademark_screenshot' => 'Screenshot or Copy of Trademark You Want To Oppose',
            'invoices' => 'Invoices',
            'advertising_material' => 'Advertising Material',
            'website_proof' => 'Website Proof',
            'social_media_proof' => 'Social Media Proof',
            'packaging_proof' => 'Packaging Proof',
            'confusion_evidence' => 'Any Confusion Evidence',
            'customer_complaints' => 'Customer Complaints',
            'screenshots' => 'Screenshots',
            'product_photos' => 'Product Photos',
            'catalogue' => 'Catalogue',
            'sales_proof' => 'Sales Proof',
            'market_proof' => 'Market Proof',
        ];
    }

    public static function opposeRequiredEvidenceGroups(): array
    {
        return [
            'ownership' => ['registration_certificate', 'trademark_application'],
            'use' => ['use_proof'],
            'opposed_mark' => ['opposed_trademark_screenshot'],
        ];
    }

    public static function legalReviewPoints(): array
    {
        return [
            'Likelihood of Confusion',
            'Similarity of Marks',
            'Similarity of Goods',
            'Similarity of Services',
            'Prior Use Rights',
            'Prior Registration Rights',
            'Passing Off Grounds',
            'Bad Faith Grounds',
            'Reputation Evidence',
        ];
    }

    public static function recommendations(): array
    {
        return [
            'Strong Opposition Case' => 'Proceed with opposition.',
            'Moderate Case' => 'Proceed with caution.',
            'Weak Case' => 'Opposition not recommended.',
        ];
    }

    public static function grounds(): array
    {
        return [
            'Section 9',
            'Section 11',
            'Prior Use Claim',
            'Prior Registration Claim',
            'Passing Off',
            'Copyright Claim',
            'Bad Faith Filing',
            'Well-known Mark Claim',
            'Dilution',
            'Misrepresentation',
            'Multiple Grounds',
            'Other',
        ];
    }

    public static function riskNotes(): array
    {
        return [
            'Low Risk' => 'Weak opposition. Strong defence available.',
            'Medium Risk' => 'Requires substantial evidence. Possible hearing.',
            'High Risk' => 'Strong prior rights claimed by opponent. Settlement may be considered.',
        ];
    }

    public static function trackingStatuses(): array
    {
        return [
            self::ADMIN_AWAITING_EVIDENCE_STAGE,
            self::ADMIN_EVIDENCE_BY_OPPONENT,
            self::ADMIN_EVIDENCE_BY_APPLICANT,
            self::ADMIN_EVIDENCE_IN_REPLY,
            self::ADMIN_EVIDENCE_FILED,
            self::ADMIN_HEARING_PREPARATION,
            self::ADMIN_HEARING_SCHEDULED,
            self::ADMIN_HEARING_ADJOURNED,
            self::ADMIN_HEARING_COMPLETED,
            self::ADMIN_DECISION_AWAITED,
            self::ADMIN_FINAL_OUTCOME,
            self::ADMIN_MATTER_CLOSED,
        ];
    }

    public static function defendSequentialStages(): array
    {
        return [
            self::ADMIN_COUNTER_STATEMENT_FILED,
            self::ADMIN_AWAITING_EVIDENCE_STAGE,
            self::ADMIN_EVIDENCE_BY_OPPONENT,
            self::ADMIN_EVIDENCE_BY_APPLICANT,
            self::ADMIN_EVIDENCE_IN_REPLY,
            self::ADMIN_EVIDENCE_FILED,
            self::ADMIN_HEARING_PREPARATION,
            self::ADMIN_HEARING_SCHEDULED,
            self::ADMIN_HEARING_COMPLETED,
            self::ADMIN_DECISION_AWAITED,
            self::ADMIN_FINAL_OUTCOME,
            self::ADMIN_MATTER_CLOSED,
        ];
    }

    public static function nextDefendStage(?string $currentStatus): ?string
    {
        return match ($currentStatus) {
            self::ADMIN_COUNTER_STATEMENT_FILED => self::ADMIN_AWAITING_EVIDENCE_STAGE,
            self::ADMIN_AWAITING_EVIDENCE_STAGE => self::ADMIN_EVIDENCE_BY_OPPONENT,
            self::ADMIN_EVIDENCE_BY_OPPONENT => self::ADMIN_EVIDENCE_BY_APPLICANT,
            self::ADMIN_EVIDENCE_BY_APPLICANT => self::ADMIN_EVIDENCE_IN_REPLY,
            self::ADMIN_EVIDENCE_IN_REPLY => self::ADMIN_EVIDENCE_FILED,
            self::ADMIN_EVIDENCE_FILED => self::ADMIN_HEARING_PREPARATION,
            self::ADMIN_HEARING_PREPARATION => self::ADMIN_HEARING_SCHEDULED,
            self::ADMIN_HEARING_SCHEDULED => self::ADMIN_HEARING_COMPLETED,
            self::ADMIN_HEARING_COMPLETED => self::ADMIN_DECISION_AWAITED,
            self::ADMIN_DECISION_AWAITED => self::ADMIN_FINAL_OUTCOME,
            default => null,
        };
    }

    public static function defendStageDescription(?string $stage): string
    {
        return match ($stage) {
            self::ADMIN_COUNTER_STATEMENT_FILED => 'The Counter Statement has been filed with the Trademark Registry. The matter is active and waiting for the next procedural stage.',
            self::ADMIN_AWAITING_EVIDENCE_STAGE => 'The matter is waiting for the next evidence stage update from the Registry.',
            self::ADMIN_EVIDENCE_BY_OPPONENT => 'The matter has moved to the opponent evidence stage.',
            self::ADMIN_EVIDENCE_BY_APPLICANT => 'The matter has moved to applicant evidence stage.',
            self::ADMIN_EVIDENCE_IN_REPLY => 'The matter has moved to evidence in reply stage.',
            self::ADMIN_EVIDENCE_FILED => 'Evidence filing has been marked as completed.',
            self::ADMIN_HEARING_PREPARATION => 'The matter has moved to hearing preparation stage.',
            self::ADMIN_HEARING_SCHEDULED => 'A hearing has been scheduled by the Trademark Registry.',
            self::ADMIN_HEARING_COMPLETED => 'The hearing has been completed.',
            self::ADMIN_DECISION_AWAITED => 'The matter is waiting for the final decision from the Trademark Registry.',
            self::ADMIN_FINAL_OUTCOME => 'The Registry decision or final update is ready to be recorded.',
            self::ADMIN_MATTER_CLOSED => 'The matter has been closed with a final outcome.',
            default => 'The matter is active.',
        };
    }

    public static function defendNextStageDescription(?string $stage): string
    {
        return match ($stage) {
            self::ADMIN_AWAITING_EVIDENCE_STAGE => 'Move the matter to this stage when Registry monitoring begins or when the next evidence update is expected.',
            self::ADMIN_EVIDENCE_BY_OPPONENT => 'Move the matter to this stage when the opponent evidence stage begins.',
            self::ADMIN_EVIDENCE_BY_APPLICANT => 'Move the matter to this stage when applicant evidence is required or filed.',
            self::ADMIN_EVIDENCE_IN_REPLY => 'Move the matter to this stage when evidence in reply begins.',
            self::ADMIN_EVIDENCE_FILED => 'Use this stage when evidence filing has been completed.',
            self::ADMIN_HEARING_PREPARATION => 'Move the matter to this stage when hearing preparation begins.',
            self::ADMIN_HEARING_SCHEDULED => 'Move the matter to this stage when a hearing date is available.',
            self::ADMIN_HEARING_COMPLETED => 'Use this stage after the Registry hearing has concluded.',
            self::ADMIN_DECISION_AWAITED => 'Move the matter to this stage once the case is awaiting final Registry decision.',
            self::ADMIN_FINAL_OUTCOME => 'Move here when the Registry decision or final update is ready to record.',
            default => 'Move the matter to the next registry stage.',
        };
    }

    public static function defendStageButtonLabel(?string $nextStage): string
    {
        return match ($nextStage) {
            self::ADMIN_EVIDENCE_FILED => 'Mark Evidence Filed & Notify Client',
            self::ADMIN_HEARING_COMPLETED => 'Mark Hearing Completed & Notify Client',
            self::ADMIN_FINAL_OUTCOME => 'Move to Final Outcome',
            null => 'Update Tracking',
            default => 'Move to ' . $nextStage . ' & Notify Client',
        };
    }

    public static function defenceFinalOutcomeOptions(): array
    {
        return [
            self::DEFENCE_CASE_SUCCEEDED => 'Defence Case Succeeded',
            self::DEFENCE_CASE_FAILED_TO_SUCCEED => 'Defence Case Failed',
            self::ADMIN_SETTLEMENT_CLOSED => 'Settlement Closed',
            self::ADMIN_WITHDRAWN => 'Withdrawn',
            self::ADMIN_OTHER => 'Other',
        ];
    }

    public static function defenceOutcomeOptions(): array
    {
        return [
            self::DEFENCE_CASE_SUCCEEDED => 'Defence Case Succeeded',
            self::DEFENCE_CASE_FAILED_TO_SUCCEED => 'Failed to Succeed',
        ];
    }

    public static function defenceOutcomeLabel(?string $status): ?string
    {
        return match ($status) {
            self::DEFENCE_CASE_SUCCEEDED => 'Defence Case Succeeded',
            self::DEFENCE_CASE_FAILED_TO_SUCCEED => 'Failed to Succeed',
            self::DEFENCE_CASE_CLOSED => 'Closed',
            null, '' => null,
            default => str_replace('_', ' ', (string) $status),
        };
    }

    public static function opposeOutcomeOptions(): array
    {
        return [
            self::OPPOSE_CASE_SUCCEEDED => 'Oppose Case Succeeded',
            self::OPPOSE_CASE_FAILED_TO_SUCCEED => 'Failed to Succeed',
            self::OPPOSE_CASE_CLOSED => 'Closed',
        ];
    }

    public static function opposeOutcomeLabel(?string $status): ?string
    {
        return match ($status) {
            self::OPPOSE_CASE_SUCCEEDED => 'Oppose Case Succeeded',
            self::OPPOSE_CASE_FAILED_TO_SUCCEED => 'Failed to Succeed',
            self::OPPOSE_CASE_CLOSED => 'Closed',
            null, '' => null,
            default => str_replace('_', ' ', (string) $status),
        };
    }

    public static function decisionOutcomeOptions(): array
    {
        return [
            self::ADMIN_OPPOSITION_ALLOWED,
            self::ADMIN_OPPOSITION_DISMISSED,
            self::ADMIN_SETTLEMENT_CLOSED,
            self::ADMIN_WITHDRAWN,
            self::ADMIN_OTHER,
        ];
    }

    public static function withdrawnByOptions(): array
    {
        return [
            self::WITHDRAWN_BY_OPPONENT => 'Opponent',
            self::WITHDRAWN_BY_APPLICANT => 'Applicant',
        ];
    }

    public static function finalOutcomeLabel(?string $outcome): ?string
    {
        return match ($outcome) {
            self::ADMIN_OPPOSITION_ALLOWED => self::ADMIN_OPPOSITION_ALLOWED,
            self::ADMIN_OPPOSITION_DISMISSED => self::ADMIN_OPPOSITION_DISMISSED,
            self::ADMIN_SETTLEMENT_CLOSED => self::ADMIN_SETTLEMENT_CLOSED,
            self::FINAL_OUTCOME_WITHDRAWN_BY_OPPONENT => 'Withdrawn by Opponent',
            self::FINAL_OUTCOME_WITHDRAWN_BY_APPLICANT => 'Withdrawn by Applicant',
            self::FINAL_OUTCOME_OTHER, self::ADMIN_OTHER => 'Other',
            self::ADMIN_WITHDRAWN => 'Withdrawn',
            null, '' => null,
            default => str_replace('_', ' ', (string) $outcome),
        };
    }

    public static function decisionFormState(?string $storedOutcome, ?string $withdrawnBy = null): array
    {
        return match ($storedOutcome) {
            self::FINAL_OUTCOME_WITHDRAWN_BY_OPPONENT => [
                'decision_status' => self::ADMIN_WITHDRAWN,
                'withdrawn_by' => self::WITHDRAWN_BY_OPPONENT,
            ],
            self::FINAL_OUTCOME_WITHDRAWN_BY_APPLICANT => [
                'decision_status' => self::ADMIN_WITHDRAWN,
                'withdrawn_by' => self::WITHDRAWN_BY_APPLICANT,
            ],
            self::FINAL_OUTCOME_OTHER => [
                'decision_status' => self::ADMIN_OTHER,
                'withdrawn_by' => $withdrawnBy,
            ],
            default => [
                'decision_status' => $storedOutcome,
                'withdrawn_by' => $withdrawnBy,
            ],
        };
    }

    public static function decisionPreview(?string $decisionStatus, ?string $withdrawnBy = null): ?array
    {
        return match ($decisionStatus) {
            self::ADMIN_OPPOSITION_ALLOWED => [
                'stored_final_outcome' => self::ADMIN_OPPOSITION_ALLOWED,
                'oppose_case_status' => self::OPPOSE_CASE_SUCCEEDED,
                'oppose_case_status_label' => 'Oppose Case Succeeded',
                'defence_case_status' => self::DEFENCE_CASE_FAILED_TO_SUCCEED,
                'defence_case_status_label' => 'Defence Failed',
                'application_opposition_status' => self::APPLICATION_OPPOSITION_SUCCEEDED,
                'application_opposition_defence_status' => self::APPLICATION_DEFENCE_FAILED,
                'application_final_opposition_result' => self::APPLICATION_FINAL_RESULT_OPPOSITION_SUCCESSFUL,
                'trademark_filing_result_label' => 'Opposition Successful',
            ],
            self::ADMIN_OPPOSITION_DISMISSED => [
                'stored_final_outcome' => self::ADMIN_OPPOSITION_DISMISSED,
                'oppose_case_status' => self::OPPOSE_CASE_FAILED_TO_SUCCEED,
                'oppose_case_status_label' => 'Failed to Succeed',
                'defence_case_status' => self::DEFENCE_CASE_SUCCEEDED,
                'defence_case_status_label' => 'Defence Succeeded',
                'application_opposition_status' => self::APPLICATION_OPPOSITION_FAILED,
                'application_opposition_defence_status' => self::APPLICATION_DEFENCE_SUCCEEDED,
                'application_final_opposition_result' => self::APPLICATION_FINAL_RESULT_DEFENCE_SUCCESSFUL,
                'trademark_filing_result_label' => 'Defence Successful',
            ],
            self::ADMIN_SETTLEMENT_CLOSED => [
                'stored_final_outcome' => self::ADMIN_SETTLEMENT_CLOSED,
                'oppose_case_status' => self::OPPOSE_CASE_CLOSED,
                'oppose_case_status_label' => 'Closed',
                'defence_case_status' => self::DEFENCE_CASE_CLOSED,
                'defence_case_status_label' => 'Closed',
                'application_opposition_status' => self::APPLICATION_SETTLEMENT_CLOSED,
                'application_opposition_defence_status' => self::APPLICATION_SETTLEMENT_CLOSED,
                'application_final_opposition_result' => self::APPLICATION_FINAL_RESULT_SETTLEMENT_CLOSED,
                'trademark_filing_result_label' => 'Settlement Closed',
            ],
            self::ADMIN_WITHDRAWN => match ($withdrawnBy) {
                self::WITHDRAWN_BY_OPPONENT => [
                    'stored_final_outcome' => self::FINAL_OUTCOME_WITHDRAWN_BY_OPPONENT,
                    'oppose_case_status' => self::OPPOSE_CASE_FAILED_TO_SUCCEED,
                    'oppose_case_status_label' => 'Failed to Succeed',
                    'defence_case_status' => self::DEFENCE_CASE_SUCCEEDED,
                    'defence_case_status_label' => 'Defence Succeeded',
                    'application_opposition_status' => self::APPLICATION_OPPOSITION_WITHDRAWN,
                    'application_opposition_defence_status' => self::APPLICATION_DEFENCE_SUCCEEDED,
                    'application_final_opposition_result' => self::APPLICATION_FINAL_RESULT_OPPOSITION_WITHDRAWN,
                    'trademark_filing_result_label' => 'Opposition Withdrawn',
                ],
                self::WITHDRAWN_BY_APPLICANT => [
                    'stored_final_outcome' => self::FINAL_OUTCOME_WITHDRAWN_BY_APPLICANT,
                    'oppose_case_status' => self::OPPOSE_CASE_SUCCEEDED,
                    'oppose_case_status_label' => 'Oppose Case Succeeded',
                    'defence_case_status' => self::DEFENCE_CASE_FAILED_TO_SUCCEED,
                    'defence_case_status_label' => 'Defence Failed',
                    'application_opposition_status' => self::APPLICATION_OPPOSITION_SUCCEEDED,
                    'application_opposition_defence_status' => self::APPLICATION_DEFENCE_FAILED,
                    'application_final_opposition_result' => self::APPLICATION_FINAL_RESULT_APPLICATION_WITHDRAWN,
                    'trademark_filing_result_label' => 'Application Withdrawn',
                ],
                default => null,
            },
            self::ADMIN_OTHER => [
                'stored_final_outcome' => self::FINAL_OUTCOME_OTHER,
                'oppose_case_status' => self::OPPOSE_CASE_CLOSED,
                'oppose_case_status_label' => 'Closed',
                'defence_case_status' => null,
                'defence_case_status_label' => 'Not auto-marked',
                'application_opposition_status' => null,
                'application_opposition_defence_status' => null,
                'application_final_opposition_result' => self::APPLICATION_FINAL_RESULT_OTHER,
                'trademark_filing_result_label' => 'Other',
            ],
            default => null,
        };
    }

    public static function applicationFinalOppositionResultLabel(?string $result): ?string
    {
        return match ($result) {
            self::APPLICATION_FINAL_RESULT_OPPOSITION_SUCCESSFUL => 'Opposition Successful',
            self::APPLICATION_FINAL_RESULT_DEFENCE_SUCCESSFUL => 'Defence Successful',
            self::APPLICATION_FINAL_RESULT_SETTLEMENT_CLOSED => 'Matter Closed by Settlement',
            self::APPLICATION_FINAL_RESULT_OPPOSITION_WITHDRAWN => 'Opposition Withdrawn',
            self::APPLICATION_FINAL_RESULT_APPLICATION_WITHDRAWN => 'Application Withdrawn',
            self::APPLICATION_FINAL_RESULT_OTHER => 'Other',
            null, '' => null,
            default => str_replace('_', ' ', (string) $result),
        };
    }

    public static function clientStageForAdminStatus(string $status): string
    {
        return match ($status) {
            self::ADMIN_APPLICATION_RECEIVED,
            self::ADMIN_DOCUMENTS_PENDING => self::CLIENT_CASE_OPENED,
            self::ADMIN_OPPOSITION_REVIEW,
            self::ADMIN_LEGAL_ANALYSIS,
            self::ADMIN_LEGAL_ANALYSIS_COMPLETED => self::CLIENT_LEGAL_REVIEW,
            self::ADMIN_EVIDENCE_COLLECTION => self::CLIENT_EVIDENCE_COLLECTION,
            'Payment Completed',
            self::CLIENT_PRICING_PAYMENT => self::CLIENT_PRICING_PAYMENT,
            self::ADMIN_NOTICE_DRAFTING,
            self::ADMIN_DRAFT_UNDER_LEGAL_REVIEW,
            self::ADMIN_CLIENT_APPROVAL_PENDING,
            self::ADMIN_COUNTER_STATEMENT_DRAFTING,
            self::ADMIN_CLIENT_APPROVAL,
            self::ADMIN_READY_FOR_FILING => self::CLIENT_DRAFTING,
            self::ADMIN_NOTICE_FILED,
            self::ADMIN_COUNTER_STATEMENT_FILED,
            self::ADMIN_AWAITING_EVIDENCE_STAGE => self::CLIENT_AWAITING_EVIDENCE_STAGE,
            self::ADMIN_COUNTER_STATEMENT_AWAITED => self::CLIENT_AWAITING_OTHER_PARTY,
            self::ADMIN_EVIDENCE_BY_OPPONENT,
            self::ADMIN_EVIDENCE_BY_APPLICANT,
            self::ADMIN_EVIDENCE_IN_REPLY,
            self::ADMIN_EVIDENCE_FILED => self::CLIENT_EVIDENCE_STAGE,
            self::ADMIN_HEARING_SCHEDULED,
            self::ADMIN_HEARING_ADJOURNED,
            self::ADMIN_HEARING_PREPARATION,
            self::ADMIN_HEARING_COMPLETED => self::CLIENT_HEARING_STAGE,
            self::ADMIN_DECISION_AWAITED,
            self::ADMIN_FINAL_OUTCOME => self::CLIENT_DECISION_AWAITED,
            self::ADMIN_MATTER_CLOSED,
            self::ADMIN_OPPOSITION_ALLOWED,
            self::ADMIN_OPPOSITION_DISMISSED,
            self::ADMIN_SETTLEMENT_CLOSED => self::CLIENT_MATTER_CLOSED,
            default => self::CLIENT_CASE_OPENED,
        };
    }

    public static function deadlineStatus(CarbonInterface $deadline): string
    {
        $daysLeft = now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false);

        if ($daysLeft >= 30) {
            return 'green';
        }

        if ($daysLeft >= 15) {
            return 'yellow';
        }

        return 'red';
    }
}
