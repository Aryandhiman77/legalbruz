<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_visitors', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_token')->unique();
            $table->string('first_path', 500);
            $table->string('last_path', 500);
            $table->unsignedBigInteger('page_views')->default(1);
            $table->dateTime('first_visited_at');
            $table->dateTime('last_visited_at');
            $table->dateTime('service_first_visited_at')->nullable()->index();
            $table->string('first_service', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_visitors');
    }
};
