<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('is_gr_closed')
                ->nullable()
                ->default(false)
                ->after('is_spv_rejected'); // bisa ubah posisi kalau mau
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('is_gr_closed');
        });
    }
};
