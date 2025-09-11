<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_type_id')->nullable()
                  ->constrained('orders')
                  ->nullOnDelete();
            $table->string('name', 100)->nullable();
            $table->string('mat_desc', 100)->nullable();
            $table->timestamptz('created_at')->nullable();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mats');
    }
};
