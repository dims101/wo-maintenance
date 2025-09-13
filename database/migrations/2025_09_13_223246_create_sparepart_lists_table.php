<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sparepart_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wo_id')->nullable()
                  ->constrained('work_orders')
                  ->nullOnDelete();

            $table->string('barcode', 30)->nullable();
            $table->foreign('barcode')->references('barcode')->on('spareparts')->nullOnDelete();

            $table->string('qty', 30)->nullable();
            $table->string('uom', 10)->nullable();

            $table->timestamptz('created_at')->nullable();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sparepart_lists');
    }
};
