<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_assignments', function (Blueprint $table) {

            $table->date('start_date')->nullable()->after('is_active');
            $table->date('finish_date')->nullable()->after('start_date');

            // Durasi dalam JAM (tanpa desimal)
            $table->unsignedInteger('duration')->nullable()->after('finish_date');

            // Minggu ke berapa (1–53)
            $table->unsignedTinyInteger('week_number')->nullable()->after('duration');

            // Tahun untuk filtering
            $table->unsignedSmallInteger('year')->nullable()->after('week_number');
        });
    }

    public function down(): void
    {
        Schema::table('team_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'start_date',
                'finish_date',
                'duration',
                'week_number',
                'year',
            ]);
        });
    }
};
