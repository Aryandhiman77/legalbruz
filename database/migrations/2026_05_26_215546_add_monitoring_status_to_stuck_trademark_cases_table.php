<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'monitoring_status')) {
                $table->string('monitoring_status')->default('Active')->after('current_trademark_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (Schema::hasColumn('stuck_trademark_cases', 'monitoring_status')) {
                $table->dropColumn('monitoring_status');
            }
        });
    }
};
