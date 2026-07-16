<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_report_client_note')) {
                $table->text('audit_report_client_note')->nullable()->after('audit_report_name');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_report_approved_at')) {
                $table->timestamp('audit_report_approved_at')->nullable()->after('audit_report_client_note');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_report_reupload_requested_at')) {
                $table->timestamp('audit_report_reupload_requested_at')->nullable()->after('audit_report_approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            foreach ([
                'audit_report_reupload_requested_at',
                'audit_report_approved_at',
                'audit_report_client_note',
            ] as $column) {
                if (Schema::hasColumn('stuck_trademark_cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
