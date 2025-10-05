<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actual_manhours', function (Blueprint $table) {
            $table->tinyInteger('shift')
                ->nullable()
                ->comment('1 = Shift 1, 2 = Shift 2, 3 = Shift 3')
                ->after('user_id');

            $table->date('date')
                ->nullable()
                ->after('shift');
        });
    }

    public function down(): void
    {
        Schema::table('actual_manhours', function (Blueprint $table) {
            $table->dropColumn(['shift', 'date']);
        });
    }
};
