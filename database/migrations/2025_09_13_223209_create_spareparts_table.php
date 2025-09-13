<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 30)->nullable()->unique();
            $table->string('code', 30)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('stock', 30)->nullable();
            $table->string('uom', 10)->nullable();

            $table->timestamptz('created_at')->nullable();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
