<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opposition_documents', function (Blueprint $table) {
            $table->string('review_status')->default('pending')->after('is_required');
            $table->text('review_note')->nullable()->after('review_status');
            $table->timestamp('reviewed_at')->nullable()->after('review_note');
            $table->string('reviewed_by')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('opposition_documents', function (Blueprint $table) {
            $table->dropColumn(['review_status', 'review_note', 'reviewed_at', 'reviewed_by']);
        });
    }
};
