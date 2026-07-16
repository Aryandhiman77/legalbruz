<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trademark_opposition_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('flow_type')->default('defend_my_trademark');
            $table->string('applicant_name');
            $table->string('trademark_name');
            $table->string('application_number');
            $table->string('trademark_class');
            $table->string('mobile_number');
            $table->string('email');
            $table->date('notice_receipt_date');
            $table->date('counter_statement_deadline');
            $table->string('deadline_status');
            $table->string('current_admin_status')->default('Application Received');
            $table->string('current_client_stage')->default('Case Opened');
            $table->string('risk_level')->nullable();
            $table->text('risk_note')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('package_name')->nullable();
            $table->decimal('package_price', 10, 2)->nullable();
            $table->json('included_services')->nullable();
            $table->json('add_ons')->nullable();
            $table->string('client_approval_status')->nullable();
            $table->text('client_change_request')->nullable();
            $table->string('draft_path')->nullable();
            $table->string('draft_name')->nullable();
            $table->string('filing_acknowledgment_path')->nullable();
            $table->string('filing_acknowledgment_name')->nullable();
            $table->date('hearing_date')->nullable();
            $table->string('final_outcome')->nullable();
            $table->text('admin_internal_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'flow_type'], 'opp_cases_user_flow_idx');
            $table->index(['current_admin_status', 'payment_status'], 'opp_cases_status_payment_idx');
        });

        Schema::create('opposition_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('trademark_opposition_cases')->onDelete('cascade');
            $table->string('document_type');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->string('uploaded_by')->default('client');
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        Schema::create('opposition_grounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('trademark_opposition_cases')->onDelete('cascade');
            $table->string('ground_name');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('opposition_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('trademark_opposition_cases')->onDelete('cascade');
            $table->string('evidence_type');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->string('uploaded_by')->default('client');
            $table->timestamps();
        });

        Schema::create('case_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('trademark_opposition_cases')->onDelete('cascade');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('changed_by')->default('system');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_status_histories');
        Schema::dropIfExists('opposition_evidence');
        Schema::dropIfExists('opposition_grounds');
        Schema::dropIfExists('opposition_documents');
        Schema::dropIfExists('trademark_opposition_cases');
    }
};
