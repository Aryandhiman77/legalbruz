<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('website_visitors') || ! Schema::hasTable('website_service_visits')) {
            return;
        }

        DB::table('website_visitors')
            ->whereNotNull('first_service')
            ->orderBy('id')
            ->chunkById(250, function ($visitors) {
                foreach ($visitors as $visitor) {
                    $serviceKey = $this->normalizeServiceKey((string) $visitor->first_service);

                    if ($serviceKey === null) {
                        continue;
                    }

                    DB::table('website_service_visits')->insertOrIgnore([
                        'website_visitor_id' => $visitor->id,
                        'service_key' => $serviceKey,
                        'first_path' => $visitor->first_path,
                        'last_path' => $visitor->last_path,
                        'page_views' => 1,
                        'first_visited_at' => $visitor->service_first_visited_at ?? $visitor->first_visited_at,
                        'last_visited_at' => $visitor->last_visited_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Historical visit records are retained if this data migration is rolled back.
    }

    private function normalizeServiceKey(string $value): ?string
    {
        if (in_array($value, array_keys(config('visitor_services', [])), true)) {
            return $value;
        }

        if (Str::contains($value, ['stuck-trademark', 'filed-and-stuck'])) {
            return 'filed_stuck_recovery';
        }

        if (Str::contains($value, ['opposition-management', 'trademark-opposition'])) {
            return 'opposition_management';
        }

        if (Str::contains($value, ['examination-reply', 'examination-report-reply'])) {
            return 'examination_report_reply';
        }

        if (Str::contains($value, ['trademark.search', 'trademark.type', 'trademark.application'])) {
            return 'trademark_registration';
        }

        return null;
    }
};
