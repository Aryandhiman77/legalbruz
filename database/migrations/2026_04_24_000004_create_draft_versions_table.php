<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draft_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('version_no')->default(1);
            $table->json('classes')->nullable();
            $table->longText('goods_services')->nullable();
            $table->string('mark_preview_path')->nullable();
            $table->string('tm_a_draft_path')->nullable();
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->string('status')->default('draft');
            $table->string('client_decision')->nullable();
            $table->text('client_comments')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_versions');
    }
};
