<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('team_assignments', function (Blueprint $table) {
            $table->foreignId('pm_id')->nullable()->after('approval_id')->constrained('preventive_maintenances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_assignments', function (Blueprint $table) {
            $table->dropForeign(['pm_id']);
            $table->dropColumn('pm_id');
        });
    }
};
