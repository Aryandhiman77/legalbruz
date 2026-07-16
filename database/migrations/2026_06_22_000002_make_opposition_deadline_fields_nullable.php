<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            $table->date('notice_receipt_date')->nullable()->change();
            $table->date('counter_statement_deadline')->nullable()->change();
            $table->string('deadline_status')->nullable()->change();
        });

        DB::table('trademark_opposition_cases')
            ->where('flow_type', 'oppose_a_trademark')
            ->update([
                'notice_receipt_date' => null,
                'counter_statement_deadline' => null,
                'deadline_status' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('trademark_opposition_cases', function (Blueprint $table) {
            $table->date('notice_receipt_date')->nullable(false)->change();
            $table->date('counter_statement_deadline')->nullable(false)->change();
            $table->string('deadline_status')->nullable(false)->change();
        });
    }
};
