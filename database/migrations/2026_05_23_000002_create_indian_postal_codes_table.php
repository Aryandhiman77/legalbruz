<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indian_postal_codes', function (Blueprint $table) {
            $table->id();
            $table->string('pincode', 6)->index();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['pincode', 'state', 'district', 'city'], 'indian_postal_codes_unique_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indian_postal_codes');
    }
};
