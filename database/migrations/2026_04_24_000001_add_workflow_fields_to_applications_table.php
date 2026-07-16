<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('service_status')->nullable()->after('status');
            $table->string('registry_status')->nullable()->after('service_status');
            $table->json('workflow_meta')->nullable()->after('members_details');
            $table->timestamp('current_stage_started_at')->nullable()->after('workflow_meta');
            $table->timestamp('approved_at')->nullable()->after('current_stage_started_at');
            $table->timestamp('onboarding_completed_at')->nullable()->after('approved_at');
            $table->timestamp('kyc_verified_at')->nullable()->after('onboarding_completed_at');
            $table->timestamp('strategy_completed_at')->nullable()->after('kyc_verified_at');
            $table->timestamp('draft_ready_at')->nullable()->after('strategy_completed_at');
            $table->timestamp('client_approved_at')->nullable()->after('draft_ready_at');
            $table->timestamp('final_payment_completed_at')->nullable()->after('client_approved_at');
            $table->timestamp('post_filing_started_at')->nullable()->after('registered_at');
            $table->string('filing_receipt_path')->nullable()->after('post_filing_started_at');
            $table->unsignedBigInteger('assigned_admin_id')->nullable()->after('filing_receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'service_status',
                'registry_status',
                'workflow_meta',
                'current_stage_started_at',
                'approved_at',
                'onboarding_completed_at',
                'kyc_verified_at',
                'strategy_completed_at',
                'draft_ready_at',
                'client_approved_at',
                'final_payment_completed_at',
                'post_filing_started_at',
                'filing_receipt_path',
                'assigned_admin_id',
            ]);
        });
    }
};
