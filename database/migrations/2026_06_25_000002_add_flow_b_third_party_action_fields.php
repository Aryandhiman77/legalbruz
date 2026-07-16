<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            $table->string('third_party_status')->default('awaiting_response')->after('counter_statement_deadline');
            $table->string('counter_statement_path')->nullable()->after('filing_acknowledgment_name');
            $table->string('counter_statement_name')->nullable()->after('counter_statement_path');
            $table->json('third_party_evidence_requests')->nullable()->after('evidence_request_note');
            $table->text('third_party_evidence_message')->nullable()->after('third_party_evidence_requests');
            $table->boolean('third_party_evidence_pending')->default(false)->after('third_party_evidence_message');
        });

        Schema::create('opposition_registry_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('trademark_opposition_cases')->onDelete('cascade');
            $table->string('update_type');
            $table->date('update_date');
            $table->text('notes')->nullable();
            $table->string('created_by')->default('admin');
            $table->timestamps();

            $table->index(['case_id', 'update_date']);
        });

        DB::table('trademark_opposition_cases')
            ->where('flow_type', 'oppose_a_trademark')
            ->whereNotNull('filing_acknowledgment_path')
            ->whereNull('counter_statement_deadline')
            ->orderBy('id')
            ->eachById(function ($case): void {
                DB::table('trademark_opposition_cases')
                    ->where('id', $case->id)
                    ->update([
                        'counter_statement_deadline' => \Carbon\Carbon::parse($case->updated_at)->addMonths(2)->toDateString(),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('opposition_registry_updates');

        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            $table->dropColumn([
                'third_party_status',
                'counter_statement_path',
                'counter_statement_name',
                'third_party_evidence_requests',
                'third_party_evidence_message',
                'third_party_evidence_pending',
            ]);
        });
    }
};
