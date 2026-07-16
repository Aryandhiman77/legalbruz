<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('trademark_opposition_cases', 'oppose_case_status')) {
                $table->string('oppose_case_status')->nullable()->after('defence_case_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (Schema::hasColumn('trademark_opposition_cases', 'oppose_case_status')) {
                $table->dropColumn('oppose_case_status');
            }
        });
    }
};
