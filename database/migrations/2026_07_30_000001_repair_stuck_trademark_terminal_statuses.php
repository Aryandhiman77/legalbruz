<?php

use App\Support\StuckTrademarkWorkflow;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stuck_trademark_cases')
            ->whereNotNull('execution_completed_at')
            ->update([
                'execution_status' => 'Execution Completed',
                'execution_sub_stage' => 'execution_completed',
            ]);

        DB::table('stuck_trademark_cases')
            ->whereNotNull('execution_completed_at')
            ->whereNull('resolved_at')
            ->whereNull('closed_at')
            ->whereNotIn('status', [
                StuckTrademarkWorkflow::MONITORING,
                StuckTrademarkWorkflow::RESOLVED,
                StuckTrademarkWorkflow::CLOSED,
            ])
            ->update([
                'status' => StuckTrademarkWorkflow::MONITORING,
                'current_stage' => 'Monitoring & Updates',
                'monitoring_status' => 'Active',
            ]);

        DB::table('stuck_trademark_cases')
            ->whereNotNull('resolved_at')
            ->whereNull('closed_at')
            ->update([
                'status' => StuckTrademarkWorkflow::RESOLVED,
                'current_stage' => 'Resolved & Closed',
                'monitoring_status' => 'Completed',
            ]);

        DB::table('stuck_trademark_cases')
            ->whereNotNull('closed_at')
            ->update([
                'status' => StuckTrademarkWorkflow::CLOSED,
                'current_stage' => 'Resolved & Closed',
                'monitoring_status' => 'Completed',
            ]);
    }

    public function down(): void
    {
        // Historical contradictory states cannot be restored safely.
    }
};
