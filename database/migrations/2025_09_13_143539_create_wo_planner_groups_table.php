<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wo_planner_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id')->nullable()
                  ->constrained('maintenance_approvals')
                  ->nullOnDelete();
            $table->foreignId('planner_group_id')->nullable()
                  ->constrained('planner_groups')
                  ->nullOnDelete();
            $table->string('status', 30)->nullable();
            $table->string('pg_reject_reason', 255)->nullable();

            $table->timestamptz('created_at')->nullable();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wo_planner_groups');
    }
};
