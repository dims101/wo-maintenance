<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id')->nullable()
                  ->constrained('maintenance_approvals')
                  ->nullOnDelete();

            $table->string('task', 50)->nullable();
            $table->boolean('is_done')->nullable();

            $table->timestamptz('created_at')->nullable();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_lists');
    }
};
