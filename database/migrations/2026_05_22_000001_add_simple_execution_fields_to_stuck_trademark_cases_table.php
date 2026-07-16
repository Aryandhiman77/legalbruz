<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_status')) {
                $table->string('execution_status')->nullable()->after('execution_scope');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_started_at')) {
                $table->timestamp('execution_started_at')->nullable()->after('execution_status');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_completed_at')) {
                $table->timestamp('execution_completed_at')->nullable()->after('execution_started_at');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'current_stage')) {
                $table->string('current_stage')->nullable()->after('execution_completed_at');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('current_stage');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'current_trademark_status')) {
                $table->string('current_trademark_status')->nullable()->after('admin_note');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_recommendation')) {
                $table->text('audit_recommendation')->nullable()->after('current_trademark_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            foreach ([
                'audit_recommendation',
                'current_trademark_status',
                'admin_note',
                'current_stage',
                'execution_completed_at',
                'execution_started_at',
                'execution_status',
            ] as $column) {
                if (Schema::hasColumn('stuck_trademark_cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
