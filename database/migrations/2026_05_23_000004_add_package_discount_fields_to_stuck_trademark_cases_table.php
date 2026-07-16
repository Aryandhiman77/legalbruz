<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_original_fee')) {
                $table->decimal('audit_original_fee', 10, 2)->nullable()->after('audit_fee');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_paid_amount')) {
                $table->decimal('audit_paid_amount', 10, 2)->nullable()->after('audit_original_fee');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_discount_amount')) {
                $table->decimal('audit_discount_amount', 10, 2)->nullable()->after('audit_paid_amount');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_coupon_label')) {
                $table->string('audit_coupon_label')->nullable()->after('audit_discount_amount');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_original_fee')) {
                $table->decimal('execution_original_fee', 10, 2)->nullable()->after('execution_fee');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_paid_amount')) {
                $table->decimal('execution_paid_amount', 10, 2)->nullable()->after('execution_original_fee');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_discount_amount')) {
                $table->decimal('execution_discount_amount', 10, 2)->nullable()->after('execution_paid_amount');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_coupon_label')) {
                $table->string('execution_coupon_label')->nullable()->after('execution_discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            foreach ([
                'audit_original_fee',
                'audit_paid_amount',
                'audit_discount_amount',
                'audit_coupon_label',
                'execution_original_fee',
                'execution_paid_amount',
                'execution_discount_amount',
                'execution_coupon_label',
            ] as $column) {
                if (Schema::hasColumn('stuck_trademark_cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
