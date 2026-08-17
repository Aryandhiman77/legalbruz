<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_service_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_visitor_id')->constrained()->cascadeOnDelete();
            $table->string('service_key', 80);
            $table->string('first_path', 500);
            $table->string('last_path', 500);
            $table->unsignedBigInteger('page_views')->default(1);
            $table->dateTime('first_visited_at');
            $table->dateTime('last_visited_at');
            $table->timestamps();

            $table->unique(['website_visitor_id', 'service_key']);
            $table->index('service_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_service_visits');
    }
};
