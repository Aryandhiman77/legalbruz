<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stuck_trademark_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->string('assigned_expert_name')->nullable();
            $table->string('applicant_name');
            $table->string('email');
            $table->string('phone');
            $table->string('trademark_name');
            $table->string('application_number')->nullable();
            $table->date('filing_date')->nullable();
            $table->string('registry_status')->nullable();
            $table->json('issue_types')->nullable();
            $table->string('urgency')->default('standard');
            $table->string('prior_attorney_name')->nullable();
            $table->string('prior_attorney_contact')->nullable();
            $table->text('problem_summary');
            $table->string('status')->default('INTAKE_SUBMITTED');
            $table->string('audit_payment_status')->default('pending');
            $table->decimal('audit_fee', 10, 2)->default(1499);
            $table->timestamp('audit_paid_at')->nullable();
            $table->string('audit_report_path')->nullable();
            $table->string('audit_report_name')->nullable();
            $table->text('audit_summary')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('execution_recommendation')->nullable();
            $table->string('execution_payment_status')->default('not_requested');
            $table->decimal('execution_fee', 10, 2)->nullable();
            $table->timestamp('execution_paid_at')->nullable();
            $table->text('execution_scope')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stuck_trademark_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('stuck_trademark_cases')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('document_type');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->string('status')->default('pending');
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stuck_trademark_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('stuck_trademark_cases')->onDelete('cascade');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('actor_type')->default('system');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stuck_trademark_status_logs');
        Schema::dropIfExists('stuck_trademark_documents');
        Schema::dropIfExists('stuck_trademark_cases');
    }
};
