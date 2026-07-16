<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examination_report_reply_cases', function (Blueprint $table) {
            if (! Schema::hasColumn('examination_report_reply_cases', 'recommendation_note')) {
                $table->text('recommendation_note')->nullable()->after('client_visible_risk_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('examination_report_reply_cases', function (Blueprint $table) {
            if (Schema::hasColumn('examination_report_reply_cases', 'recommendation_note')) {
                $table->dropColumn('recommendation_note');
            }
        });
    }
};
