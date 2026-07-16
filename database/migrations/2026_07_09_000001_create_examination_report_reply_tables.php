<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_report_reply_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('case_number')->unique();
            $table->string('applicant_name');
            $table->string('trademark_name');
            $table->string('application_number');
            $table->string('trademark_class');
            $table->date('application_filing_date');
            $table->string('current_status');
            $table->boolean('filed_through_legalbruz');
            $table->date('exam_report_receipt_date');
            $table->date('reply_deadline');
            $table->string('deadline_status')->default('green');
            $table->string('current_admin_status');
            $table->string('current_client_stage');
            $table->string('case_status')->default('active');
            $table->json('objection_types')->nullable();
            $table->string('portal_label')->nullable();
            $table->json('section_9_reasons')->nullable();
            $table->text('section_11_note')->nullable();
            $table->json('section_11_details')->nullable();
            $table->json('formal_objection_reasons')->nullable();
            $table->boolean('evidence_required')->nullable();
            $table->json('evidence_intake')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('risk_reason')->nullable();
            $table->text('client_visible_risk_note')->nullable();
            $table->string('package_type')->nullable();
            $table->decimal('package_price', 10, 2)->nullable();
            $table->text('package_description')->nullable();
            $table->json('included_services')->nullable();
            $table->json('add_ons')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('draft_status')->nullable();
            $table->string('client_approval_status')->nullable();
            $table->date('reply_filing_date')->nullable();
            $table->string('acknowledgment_file')->nullable();
            $table->string('registry_update_type')->nullable();
            $table->date('registry_update_date')->nullable();
            $table->date('hearing_date')->nullable();
            $table->time('hearing_time')->nullable();
            $table->string('hearing_mode')->nullable();
            $table->string('hearing_link_or_location')->nullable();
            $table->decimal('hearing_package_price', 10, 2)->nullable();
            $table->string('hearing_payment_status')->nullable();
            $table->string('final_outcome')->nullable();
            $table->text('final_note_to_client')->nullable();
            $table->text('client_visible_note')->nullable();
            $table->text('internal_client_note')->nullable();
            $table->text('internal_tracking_note')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('examination_reply_stage_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('examination_report_reply_cases')->cascadeOnDelete();
            $table->string('from_stage')->nullable();
            $table->string('to_stage')->nullable();
            $table->text('client_message')->nullable();
            $table->text('internal_note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('examination_reply_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('examination_report_reply_cases')->cascadeOnDelete();
            $table->foreignId('stage_request_id')->nullable()->constrained('examination_reply_stage_requests')->nullOnDelete();
            $table->string('document_type');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('visibility')->default('client');
            $table->string('uploaded_by')->default('client');
            $table->string('review_status')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });

        Schema::create('examination_reply_requested_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('examination_report_reply_cases')->cascadeOnDelete();
            $table->foreignId('stage_request_id')->nullable()->constrained('examination_reply_stage_requests')->nullOnDelete();
            $table->string('document_name');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_uploaded_by_client')->default(false);
            $table->string('uploaded_file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('examination_reply_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('examination_report_reply_cases')->cascadeOnDelete();
            $table->string('old_admin_status')->nullable();
            $table->string('new_admin_status');
            $table->string('old_client_stage')->nullable();
            $table->string('new_client_stage');
            $table->text('note')->nullable();
            $table->string('changed_by')->default('system');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->timestamps();
        });

        Schema::create('examination_reply_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('examination_report_reply_cases')->cascadeOnDelete();
            $table->string('draft_file_path');
            $table->string('original_name');
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('uploaded');
            $table->text('client_comment')->nullable();
            $table->string('uploaded_by')->default('admin');
            $table->timestamps();
        });

        Schema::create('examination_reply_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('examination_report_reply_cases')->cascadeOnDelete();
            $table->string('notification_type');
            $table->string('channel')->default('in_app');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('sent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_reply_notification_logs');
        Schema::dropIfExists('examination_reply_drafts');
        Schema::dropIfExists('examination_reply_status_histories');
        Schema::dropIfExists('examination_reply_requested_documents');
        Schema::dropIfExists('examination_reply_documents');
        Schema::dropIfExists('examination_reply_stage_requests');
        Schema::dropIfExists('examination_report_reply_cases');
    }
};
