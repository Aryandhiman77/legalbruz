<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('trademark_opposition_cases')
            ->where('flow_type', 'oppose_a_trademark')
            ->whereNotNull('filing_acknowledgment_path')
            ->where('current_admin_status', 'Notice of Opposition Filed')
            ->orderBy('id')
            ->eachById(function ($case): void {
                $timestamp = now();

                DB::table('trademark_opposition_cases')
                    ->where('id', $case->id)
                    ->update([
                        'current_admin_status' => 'Counter Statement Awaited',
                        'current_client_stage' => 'Awaiting Third Party Action',
                        'third_party_status' => 'awaiting_response',
                        'updated_at' => $timestamp,
                    ]);

                DB::table('case_status_histories')->insert([
                    'case_id' => $case->id,
                    'old_status' => 'Notice of Opposition Filed',
                    'new_status' => 'Counter Statement Awaited',
                    'changed_by' => 'system',
                    'note' => 'Existing filed Flow B matter advanced to Awaiting Third Party Action.',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            });
    }

    public function down(): void
    {
        // This workflow advancement should not move active matters backwards.
    }
};
