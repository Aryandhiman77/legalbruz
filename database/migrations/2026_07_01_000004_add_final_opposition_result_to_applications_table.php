<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'final_opposition_result')) {
                $table->string('final_opposition_result')->nullable()->after('opposition_defence_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'final_opposition_result')) {
                $table->dropColumn('final_opposition_result');
            }
        });
    }
};
