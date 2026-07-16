<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('trademark_opposition_cases', 'final_client_message')) {
                $table->text('final_client_message')->nullable()->after('final_outcome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (Schema::hasColumn('trademark_opposition_cases', 'final_client_message')) {
                $table->dropColumn('final_client_message');
            }
        });
    }
};
