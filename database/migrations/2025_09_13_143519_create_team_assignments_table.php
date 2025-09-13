<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id')->nullable()
                  ->constrained('maintenance_approvals')
                  ->nullOnDelete();
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->boolean('is_pic')->nullable();

            $table->timestamptz('created_at')->nullable();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_assignments');
    }
};
