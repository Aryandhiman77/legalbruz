<?php

use App\Support\ExaminationReportReplyWorkflow as Workflow;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('examination_report_reply_cases')
            ->where('current_admin_status', Workflow::ADMIN_PAYMENT_COMPLETED)
            ->where('payment_status', 'paid')
            ->update(['current_client_stage' => Workflow::CLIENT_DRAFT_APPROVAL]);
    }

    public function down(): void
    {
        DB::table('examination_report_reply_cases')
            ->where('current_admin_status', Workflow::ADMIN_PAYMENT_COMPLETED)
            ->where('payment_status', 'paid')
            ->where('current_client_stage', Workflow::CLIENT_DRAFT_APPROVAL)
            ->update(['current_client_stage' => Workflow::CLIENT_PRICING_PAYMENT]);
    }
};
