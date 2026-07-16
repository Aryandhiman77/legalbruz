<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('trademark_opposition_cases', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('trademark_opposition_cases', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_reference');
            }

            if (!Schema::hasColumn('trademark_opposition_cases', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('transaction_id');
            }

            if (!Schema::hasColumn('trademark_opposition_cases', 'original_package_price')) {
                $table->decimal('original_package_price', 10, 2)->nullable()->after('package_price');
            }

            if (!Schema::hasColumn('trademark_opposition_cases', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->nullable()->after('original_package_price');
            }

            if (!Schema::hasColumn('trademark_opposition_cases', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->nullable()->after('paid_amount');
            }

            if (!Schema::hasColumn('trademark_opposition_cases', 'coupon_label')) {
                $table->string('coupon_label')->nullable()->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            foreach (['payment_reference', 'transaction_id', 'paid_at', 'original_package_price', 'paid_amount', 'discount_amount', 'coupon_label'] as $column) {
                if (Schema::hasColumn('trademark_opposition_cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
