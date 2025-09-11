<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wo_id')->nullable()
                  ->constrained('work_orders')
                  ->nullOnDelete();
            $table->foreignId('mat_id')->nullable()
                  ->constrained('mats')
                  ->nullOnDelete();

            $table->timestamp('start')->nullable();
            $table->timestamp('finish')->nullable();
            $table->string('progress', 5)->nullable();
            $table->boolean('is_closed')->nullable();
            $table->boolean('is_rejected')->nullable();
            $table->string('reject_reason', 255)->nullable();
            $table->boolean('is_received')->nullable();
            $table->string('delay_reason', 255)->nullable();

            $table->timestamptz('created_at')->nullable();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_approvals');
    }
};
