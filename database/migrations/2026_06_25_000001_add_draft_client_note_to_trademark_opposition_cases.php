<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('trademark_opposition_cases', 'draft_client_note')) {
                $table->text('draft_client_note')->nullable()->after('client_change_request');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (Schema::hasColumn('trademark_opposition_cases', 'draft_client_note')) {
                $table->dropColumn('draft_client_note');
            }
        });
    }
};
