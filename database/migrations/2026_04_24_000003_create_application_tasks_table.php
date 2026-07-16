<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('task_code');
            $table->string('task_group')->nullable();
            $table->string('title');
            $table->string('assignee_type')->default('user');
            $table->string('status')->default('pending');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'task_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_tasks');
    }
};
