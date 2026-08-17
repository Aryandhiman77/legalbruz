<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stuck_trademark_cases')
            ->whereNotNull('closed_at')
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => DB::raw('closed_at'),
            ]);

        DB::table('stuck_trademark_cases')
            ->whereNotNull('closed_at')
            ->whereNotNull('resolved_at')
            ->whereColumn('closed_at', '<', 'resolved_at')
            ->update([
                'closed_at' => DB::raw('resolved_at'),
            ]);
    }

    public function down(): void
    {
        // Historical contradictory timestamps cannot be restored safely.
    }
};
