<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trademark_execution_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('stuck_trademark_cases')->onDelete('cascade');
            $table->string('action_name');
            $table->boolean('is_required')->default(false);
            $table->string('status')->default('Pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->unique(['case_id', 'action_name']);
        });

        Schema::create('trademark_execution_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('stuck_trademark_cases')->onDelete('cascade');
            $table->string('title');
            $table->text('note')->nullable();
            $table->string('stage')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('visible_to_client')->default(false);
            $table->timestamps();
        });

        Schema::create('trademark_execution_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('stuck_trademark_cases')->onDelete('cascade');
            $table->string('document_title');
            $table->string('document_type')->nullable();
            $table->string('file_path');
            $table->text('admin_note')->nullable();
            $table->boolean('visible_to_client')->default(false);
            $table->timestamps();
        });

        Schema::create('trademark_document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('stuck_trademark_cases')->onDelete('cascade');
            $table->string('document_name');
            $table->text('reason')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('Requested');
            $table->string('uploaded_file')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trademark_document_requests');
        Schema::dropIfExists('trademark_execution_documents');
        Schema::dropIfExists('trademark_execution_updates');
        Schema::dropIfExists('trademark_execution_actions');
    }
};
