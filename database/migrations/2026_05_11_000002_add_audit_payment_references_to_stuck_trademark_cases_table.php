<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_payment_reference')) {
                $table->string('audit_payment_reference')->nullable()->after('audit_paid_at');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'audit_transaction_id')) {
                $table->string('audit_transaction_id')->nullable()->after('audit_payment_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (Schema::hasColumn('stuck_trademark_cases', 'audit_transaction_id')) {
                $table->dropColumn('audit_transaction_id');
            }

            if (Schema::hasColumn('stuck_trademark_cases', 'audit_payment_reference')) {
                $table->dropColumn('audit_payment_reference');
            }
        });
    }
};
