<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'trademark_class')) {
                $table->string('trademark_class')->nullable()->after('application_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (Schema::hasColumn('stuck_trademark_cases', 'trademark_class')) {
                $table->dropColumn('trademark_class');
            }
        });
    }
};
