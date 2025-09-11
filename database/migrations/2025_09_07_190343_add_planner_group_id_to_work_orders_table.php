<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('planner_group_id')
                  ->nullable()
                  ->constrained('planner_groups')
                  ->nullOnDelete()
                  ->after('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['planner_group_id']);
            $table->dropColumn('planner_group_id');
        });
    }
};
