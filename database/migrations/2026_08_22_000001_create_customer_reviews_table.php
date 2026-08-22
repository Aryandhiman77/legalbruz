<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 120);
            $table->string('customer_title', 160);
            $table->text('review');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();
        DB::table('customer_reviews')->insert([
            [
                'customer_name' => 'Rahul Patel',
                'customer_title' => 'Founder, Tech Startup',
                'review' => 'Fantastic service! My trademark got filed in just 2 days. The entire process was transparent and hassle-free. Highly recommended!',
                'rating' => 5,
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer_name' => 'Priya Kapoor',
                'customer_title' => 'Fashion Designer',
                'review' => 'Excellent support from their team. They guided me through every step and my brand is now officially registered!',
                'rating' => 5,
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer_name' => 'Amit Kumar',
                'customer_title' => 'E-commerce Business',
                'review' => 'Best decision for my business. Their pricing is transparent and the support is outstanding. 5-star service!',
                'rating' => 5,
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_reviews');
    }
};
