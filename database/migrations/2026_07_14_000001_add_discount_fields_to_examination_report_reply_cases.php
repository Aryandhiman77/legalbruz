<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examination_report_reply_cases', function (Blueprint $table) {
            $table->decimal('original_package_price', 10, 2)->nullable()->after('package_price');
            $table->decimal('paid_amount', 10, 2)->nullable()->after('paid_at');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('paid_amount');
            $table->string('coupon_label')->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('examination_report_reply_cases', function (Blueprint $table) {
            $table->dropColumn([
                'original_package_price',
                'paid_amount',
                'discount_amount',
                'coupon_label',
            ]);
        });
    }
};
