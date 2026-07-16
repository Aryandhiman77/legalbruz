<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('trademark_opposition_cases', 'withdrawn_by')) {
                $table->string('withdrawn_by')->nullable()->after('final_outcome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (Schema::hasColumn('trademark_opposition_cases', 'withdrawn_by')) {
                $table->dropColumn('withdrawn_by');
            }
        });
    }
};
