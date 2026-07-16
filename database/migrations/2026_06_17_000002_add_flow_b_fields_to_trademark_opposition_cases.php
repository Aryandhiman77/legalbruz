<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            $table->string('trademark_you_own')->nullable()->after('flow_type');
            $table->string('trademark_to_oppose')->nullable()->after('trademark_you_own');
            $table->string('opposed_application_number')->nullable()->after('trademark_to_oppose');
            $table->text('conflict_reason')->nullable()->after('opposed_application_number');
            $table->string('opposed_applicant_name')->nullable()->after('conflict_reason');
            $table->string('user_business_name')->nullable()->after('opposed_applicant_name');
            $table->string('recommendation_level')->nullable()->after('risk_note');
            $table->text('recommendation_note')->nullable()->after('recommendation_level');
            $table->boolean('recommendation_note_visible')->default(false)->after('recommendation_note');
            $table->decimal('total_amount', 10, 2)->nullable()->after('package_price');
        });

        Schema::create('legal_review_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('trademark_opposition_cases')->onDelete('cascade');
            $table->string('review_point');
            $table->text('admin_note')->nullable();
            $table->boolean('is_client_visible')->default(false);
            $table->timestamps();
        });

        Schema::create('draft_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('trademark_opposition_cases')->onDelete('cascade');
            $table->string('draft_type')->default('notice_of_opposition');
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedInteger('version')->default(1);
            $table->string('uploaded_by')->default('admin');
            $table->string('client_status')->default('pending');
            $table->text('client_comment')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('trademark_opposition_cases')->onDelete('cascade');
            $table->string('notification_type');
            $table->string('channel');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('queued');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('draft_documents');
        Schema::dropIfExists('legal_review_points');

        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            $table->dropColumn([
                'trademark_you_own',
                'trademark_to_oppose',
                'opposed_application_number',
                'conflict_reason',
                'opposed_applicant_name',
                'user_business_name',
                'recommendation_level',
                'recommendation_note',
                'recommendation_note_visible',
                'total_amount',
            ]);
        });
    }
};
