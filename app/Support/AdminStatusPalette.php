<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminStatusPalette
{
    private const PALETTES = [
        'new' => [
            'hue' => 199,
            'saturation' => 78,
            'label' => 'New / submitted',
        ],
        'progress' => [
            'hue' => 217,
            'saturation' => 76,
            'label' => 'In progress',
        ],
        'pending' => [
            'hue' => 40,
            'saturation' => 82,
            'label' => 'Pending / waiting',
        ],
        'attention' => [
            'hue' => 24,
            'saturation' => 82,
            'label' => 'Action required',
        ],
        'success' => [
            'hue' => 160,
            'saturation' => 66,
            'label' => 'Successful / active',
        ],
        'milestone' => [
            'hue' => 239,
            'saturation' => 70,
            'label' => 'Filed / milestone',
        ],
        'scheduled' => [
            'hue' => 262,
            'saturation' => 70,
            'label' => 'Scheduled',
        ],
        'danger' => [
            'hue' => 350,
            'saturation' => 76,
            'label' => 'Blocked / unsuccessful',
        ],
        'neutral' => [
            'hue' => 215,
            'saturation' => 18,
            'label' => 'Draft / inactive',
        ],
    ];

    public static function normalize(string $status): string
    {
        return Str::of($status)->replace(['_', '-'], ' ')->squish()->lower()->toString();
    }

    public static function colors(string $status): array
    {
        $palette = self::PALETTES[self::scheme($status)];
        $hash = (int) sprintf('%u', crc32(self::normalize($status) ?: 'unknown'));
        $hueOffset = (($hash % 101) - 50) / 10;
        $hue = round(fmod($palette['hue'] + $hueOffset + 360, 360), 1);
        $saturation = max(14, min(86, $palette['saturation'] + (($hash >> 8) % 7) - 3));
        $backgroundLightness = 94 + (($hash >> 12) % 2);

        return [
            'background' => "hsl({$hue} {$saturation}% {$backgroundLightness}%)",
            'border' => "hsl({$hue} {$saturation}% 79%)",
            'text' => "hsl({$hue} ".max(28, $saturation - 8).'% 29%)',
            'dot' => "hsl({$hue} {$saturation}% 52%)",
            'label' => $palette['label'],
        ];
    }

    public static function scheme(string $status): string
    {
        $status = self::normalize($status);

        return match (true) {
            Str::contains($status, [
                'reject', 'failed', 'abandoned', 'expired', 'cancelled', 'canceled',
                'withdrawn', 'dismissed', 'blocked', 'declined', 'missed',
            ]) => 'danger',
            Str::contains($status, [
                'reupload', 'changes requested', 'action required', 'further action',
                'documents pending', 'evidence requested',
            ]) => 'attention',
            Str::contains($status, ['scheduled', 'upcoming', 'planned', 'hearing issued']) => 'scheduled',
            Str::contains($status, [
                'filed', 'registered', 'acknowledgment uploaded', 'accepted advertised',
                'ready for filing',
            ]) => 'milestone',
            Str::contains($status, [
                'approved', 'published', 'resolved', 'complete', 'completed', 'verified',
                'paid', 'hired', 'offered', 'open', 'accepted', 'allowed', 'successful',
            ]) => 'success',
            Str::contains($status, [
                'pending', 'awaiting', 'on hold', 'payment due', 'decision awaited',
            ]) => 'pending',
            Str::contains($status, [
                'application received', 'new', 'submitted', 'uploaded', 'intake',
            ]) => 'new',
            Str::contains($status, [
                'review', 'progress', 'active', 'processing', 'shortlisted', 'interview',
                'onboarding', 'drafting', 'analysis', 'preparation', 'evidence',
                'monitoring', 'strategy', 'follow up', 'follow-up',
            ]) => 'progress',
            default => 'neutral',
        };
    }

    public static function schemeLabel(string $status): string
    {
        return self::colors($status)['label'];
    }

    public static function style(string $status): string
    {
        $colors = self::colors($status);

        return "--status-bg:{$colors['background']};--status-border:{$colors['border']};--status-text:{$colors['text']};--status-dot:{$colors['dot']}";
    }

    public static function optionStyle(string $status): string
    {
        $colors = self::colors($status);

        return "background-color:{$colors['background']};color:{$colors['text']}";
    }
}
