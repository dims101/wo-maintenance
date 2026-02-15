<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actual_manhours', function (Blueprint $table) {
            $table->foreignId('pm_id')->nullable()->after('wo_id')->constrained('preventive_maintenances')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('actual_manhours', function (Blueprint $table) {
            $table->dropForeign(['pm_id']);
            $table->dropColumn('pm_id');
        });
    }
};
