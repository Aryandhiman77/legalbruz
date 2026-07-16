<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indian_postal_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('indian_postal_codes', 'country')) {
                $table->string('country')->nullable()->after('pincode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('indian_postal_codes', function (Blueprint $table) {
            if (Schema::hasColumn('indian_postal_codes', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
