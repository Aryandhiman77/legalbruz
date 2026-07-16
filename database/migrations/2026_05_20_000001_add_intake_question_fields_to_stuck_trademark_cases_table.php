<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'previous_attorney_details')) {
                $table->text('previous_attorney_details')->nullable()->after('prior_attorney_contact');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'notices_received')) {
                $table->text('notices_received')->nullable()->after('previous_attorney_details');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'hearing_notices_missed')) {
                $table->text('hearing_notices_missed')->nullable()->after('notices_received');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'status_unchanged_since')) {
                $table->string('status_unchanged_since')->nullable()->after('hearing_notices_missed');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'objection_or_hearing_notice_received')) {
                $table->string('objection_or_hearing_notice_received')->nullable()->after('status_unchanged_since');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'previous_attorney_explained_delay')) {
                $table->string('previous_attorney_explained_delay')->nullable()->after('objection_or_hearing_notice_received');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'correction_requirement_informed')) {
                $table->string('correction_requirement_informed')->nullable()->after('previous_attorney_explained_delay');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            foreach ([
                'correction_requirement_informed',
                'previous_attorney_explained_delay',
                'objection_or_hearing_notice_received',
                'status_unchanged_since',
                'hearing_notices_missed',
                'notices_received',
                'previous_attorney_details',
            ] as $column) {
                if (Schema::hasColumn('stuck_trademark_cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
