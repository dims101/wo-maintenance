<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_approvals', function (Blueprint $table) {

            // Total durasi dalam JAM (tanpa desimal)
            $table->unsignedInteger('total_duration')
                ->nullable()
                ->after('progress'); // sesuaikan posisi kalau perlu
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_approvals', function (Blueprint $table) {
            $table->dropColumn('total_duration');
        });
    }
};
