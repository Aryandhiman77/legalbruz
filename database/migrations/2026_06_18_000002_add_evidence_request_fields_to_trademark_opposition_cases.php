<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            $table->json('requested_evidence_types')->nullable()->after('admin_internal_notes');
            $table->text('evidence_request_note')->nullable()->after('requested_evidence_types');
            $table->date('first_use_date')->nullable()->after('evidence_request_note');
        });
    }

    public function down(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            $table->dropColumn(['requested_evidence_types', 'evidence_request_note', 'first_use_date']);
        });
    }
};
