<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trademark_results', function (Blueprint $table) {
    $table->id();

    $table->string('search_keyword')->nullable();
    $table->string('application_id')->unique();
    $table->string('application_date')->nullable();
    $table->text('trademark_name')->nullable();
    $table->text('proprietor')->nullable();
    $table->string('status')->nullable();
    $table->string('sub_status')->nullable();
    $table->string('class')->nullable();
    $table->string('type')->nullable();
    $table->text('attorney')->nullable();
    $table->string('state')->nullable();
    $table->string('country')->nullable();
    $table->string('filing_mode')->nullable();
    $table->string('branch_office')->nullable();
    $table->string('ip_office')->nullable();
    $table->string('used_since')->nullable();
    $table->string('valid_upto')->nullable();
    $table->longText('description')->nullable();
    $table->text('image_url')->nullable();
    $table->text('detail_url')->nullable();
    $table->text('source_url')->nullable();

    $table->json('validation_errors')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trademark_results');
    }
};
