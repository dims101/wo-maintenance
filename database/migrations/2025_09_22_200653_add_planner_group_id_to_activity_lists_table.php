<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_lists', function (Blueprint $table) {
            $table->foreignId('planner_group_id')
                ->nullable()
                ->constrained('planner_groups')
                ->nullOnDelete()
                ->after('approval_id');
        });
    }

    public function down(): void
    {
        Schema::table('activity_lists', function (Blueprint $table) {
            $table->dropForeign(['planner_group_id']);
            $table->dropColumn('planner_group_id');
        });
    }
};
