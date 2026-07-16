<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examination_reply_documents', function (Blueprint $table) {
            $table->string('document_title')->nullable()->after('document_type');
            $table->text('document_note')->nullable()->after('review_note');
            $table->text('remarks')->nullable()->after('document_note');
            $table->string('stage_key')->nullable()->after('remarks')->index();
            $table->boolean('is_draft')->default(false)->after('stage_key')->index();
            $table->json('metadata')->nullable()->after('is_draft');
        });

        Schema::create('examination_reply_stage_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('examination_report_reply_cases')->cascadeOnDelete();
            $table->string('stage_key')->index();
            $table->string('admin_status');
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();

            $table->unique(['case_id', 'stage_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_reply_stage_drafts');

        Schema::table('examination_reply_documents', function (Blueprint $table) {
            $table->dropColumn([
                'document_title',
                'document_note',
                'remarks',
                'stage_key',
                'is_draft',
                'metadata',
            ]);
        });
    }
};
