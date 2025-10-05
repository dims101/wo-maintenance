<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_lists', function (Blueprint $table) {
            $table->foreignId('pm_id')
                ->nullable()
                ->after('approval_id')
                ->constrained('preventive_maintenances')
                ->cascadeOnDelete();
        });

        Schema::table('sparepart_lists', function (Blueprint $table) {
            $table->foreignId('pm_id')
                ->nullable()
                ->after('wo_id')
                ->constrained('preventive_maintenances')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_lists', function (Blueprint $table) {
            $table->dropForeign(['pm_id']);
            $table->dropColumn('pm_id');
        });

        Schema::table('sparepart_lists', function (Blueprint $table) {
            $table->dropForeign(['pm_id']);
            $table->dropColumn('pm_id');
        });
    }
};
