<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actual_manhours', function (Blueprint $table) {
            $table->foreignId('wo_id')
                ->nullable()
                ->after('user_id')
                ->constrained('work_orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('actual_manhours', function (Blueprint $table) {
            $table->dropForeign(['wo_id']);
            $table->dropColumn('wo_id');
        });
    }
};
