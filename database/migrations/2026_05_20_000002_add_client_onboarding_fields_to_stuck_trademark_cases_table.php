<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'business_name')) {
                $table->string('business_name')->nullable()->after('applicant_name');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'applicant_address')) {
                $table->text('applicant_address')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'filing_channel')) {
                $table->string('filing_channel')->nullable()->after('prior_attorney_contact');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'received_notices')) {
                $table->string('received_notices')->nullable()->after('filing_channel');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'replies_filed_earlier')) {
                $table->string('replies_filed_earlier')->nullable()->after('received_notices');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'onboarding_issue_types')) {
                $table->json('onboarding_issue_types')->nullable()->after('replies_filed_earlier');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            foreach ([
                'onboarding_issue_types',
                'replies_filed_earlier',
                'received_notices',
                'filing_channel',
                'applicant_address',
                'business_name',
            ] as $column) {
                if (Schema::hasColumn('stuck_trademark_cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
