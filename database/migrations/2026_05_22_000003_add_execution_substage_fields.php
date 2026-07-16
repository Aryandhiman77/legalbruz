<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_sub_stage')) {
                $table->string('execution_sub_stage')->nullable()->after('execution_status');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_additional_action_required')) {
                $table->boolean('execution_additional_action_required')->default(false)->after('execution_sub_stage');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_additional_action_name')) {
                $table->string('execution_additional_action_name')->nullable()->after('execution_additional_action_required');
            }

            if (!Schema::hasColumn('stuck_trademark_cases', 'execution_additional_action_description')) {
                $table->text('execution_additional_action_description')->nullable()->after('execution_additional_action_name');
            }
        });

        Schema::table('trademark_execution_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('trademark_execution_documents', 'execution_stage')) {
                $table->string('execution_stage')->nullable()->after('document_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trademark_execution_documents', function (Blueprint $table) {
            if (Schema::hasColumn('trademark_execution_documents', 'execution_stage')) {
                $table->dropColumn('execution_stage');
            }
        });

        Schema::table('stuck_trademark_cases', function (Blueprint $table) {
            foreach ([
                'execution_additional_action_description',
                'execution_additional_action_name',
                'execution_additional_action_required',
                'execution_sub_stage',
            ] as $column) {
                if (Schema::hasColumn('stuck_trademark_cases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
