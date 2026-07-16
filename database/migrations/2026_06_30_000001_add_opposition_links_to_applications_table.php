<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'opposition_application_id')) {
                $table->foreignId('opposition_application_id')->nullable()->constrained('trademark_opposition_cases')->nullOnDelete()->after('application_number');
            }
            if (!Schema::hasColumn('applications', 'opposition_status')) {
                $table->string('opposition_status')->nullable()->after('opposition_application_id');
            }
            if (!Schema::hasColumn('applications', 'opposition_defence_case_id')) {
                $table->foreignId('opposition_defence_case_id')->nullable()->constrained('trademark_opposition_cases')->nullOnDelete()->after('opposition_status');
            }
            if (!Schema::hasColumn('applications', 'opposition_defence_status')) {
                $table->string('opposition_defence_status')->nullable()->after('opposition_defence_case_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'opposition_defence_status')) {
                $table->dropColumn('opposition_defence_status');
            }
            if (Schema::hasColumn('applications', 'opposition_defence_case_id')) {
                $table->dropForeign(['opposition_defence_case_id']);
                $table->dropColumn('opposition_defence_case_id');
            }
            if (Schema::hasColumn('applications', 'opposition_status')) {
                $table->dropColumn('opposition_status');
            }
            if (Schema::hasColumn('applications', 'opposition_application_id')) {
                $table->dropForeign(['opposition_application_id']);
                $table->dropColumn('opposition_application_id');
            }
        });
    }
};
