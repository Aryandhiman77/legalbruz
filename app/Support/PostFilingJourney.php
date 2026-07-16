<?php

namespace App\Support;

use App\Models\Application;

class PostFilingJourney
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const OPPOSED = 'opposed';

    public static function stages(Application $application): array
    {
        return collect([
            'filed' => [
                'number' => 1,
                'title' => 'Filed',
                'icon' => 'check-circle',
                'copy' => 'Trademark application has been filed with the registry.',
            ],
            'vienna_codification' => [
                'number' => 2,
                'title' => 'Vienna Codification',
                'icon' => 'image',
                'copy' => 'Logo marks are classified under the Vienna codification system.',
                'logo_only' => true,
            ],
            'formalities_check' => [
                'number' => 3,
                'title' => 'Formalities Check',
                'icon' => 'clipboard-check',
                'copy' => 'Registry checks whether filing details and documents are formally complete.',
            ],
            'marked_for_examination' => [
                'number' => 4,
                'title' => 'Marked for Examination',
                'icon' => 'search',
                'copy' => 'Application is assigned for registry examination.',
            ],
            'examination_report' => [
                'number' => 5,
                'title' => 'Examination Report',
                'icon' => 'file-warning',
                'copy' => 'Registry examination report or response stage.',
            ],
            'accepted_advertised' => [
                'number' => 6,
                'title' => 'Accepted & Advertised',
                'icon' => 'megaphone',
                'copy' => 'Application is accepted and advertised for opposition period.',
            ],
            'registered' => [
                'number' => 7,
                'title' => 'Registered®',
                'icon' => 'badge-check',
                'copy' => 'Trademark registration is completed.',
            ],
        ])
            ->reject(fn (array $stage) => ($stage['logo_only'] ?? false) && blank($application->logo_path))
            ->map(fn (array $stage, string $key) => array_merge($stage, ['key' => $key]))
            ->values()
            ->all();
    }

    public static function stage(Application $application, string $stageKey): ?array
    {
        return collect(static::stages($application))->firstWhere('key', $stageKey);
    }

    public static function meta(Application $application): array
    {
        return data_get($application->workflow_meta ?? [], 'post_filing_journey', []);
    }

    public static function statusFor(Application $application, string $stageKey): string
    {
        return data_get(static::meta($application), "stages.$stageKey.status")
            ?: ($stageKey === 'filed' ? static::COMPLETED : static::PENDING);
    }

    public static function activeStageKey(Application $application): ?string
    {
        foreach (static::stages($application) as $stage) {
            if (static::statusFor($application, $stage['key']) !== static::COMPLETED) {
                return $stage['key'];
            }
        }

        return collect(static::stages($application))->last()['key'] ?? null;
    }

    public static function previousIncompleteStage(Application $application, string $stageKey): ?array
    {
        foreach (static::stages($application) as $stage) {
            if ($stage['key'] === $stageKey) {
                return null;
            }

            if (static::statusFor($application, $stage['key']) !== static::COMPLETED) {
                return $stage;
            }
        }

        return null;
    }

    public static function previousStagesComplete(Application $application, string $stageKey): bool
    {
        return static::previousIncompleteStage($application, $stageKey) === null;
    }

    public static function documentsRequested(Application $application, string $stageKey): bool
    {
        return (bool) data_get(static::meta($application), "stages.$stageKey.documents_requested");
    }

    public static function documentsSubmitted(Application $application, string $stageKey): bool
    {
        return filled(data_get(static::meta($application), "stages.$stageKey.documents_submitted_at"));
    }

    public static function documentType(string $stageKey): string
    {
        return 'post_filing_' . $stageKey;
    }
}
