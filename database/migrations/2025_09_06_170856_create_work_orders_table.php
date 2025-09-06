<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('notification_number', 50)->nullable();
            $table->timestampTz('notification_date', 0)->nullable();
            $table->string('priority', 20)->nullable();
            $table->string('work_desc', 255)->nullable();
            $table->timestampTz('malfunction_start', 0)->nullable();
            $table->foreignId('equipment_id')->nullable()->constrained('equipments')->nullOnDelete();
            $table->boolean('is_breakdown')->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('req_dept_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('req_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('urgent_level', 20)->nullable();
            $table->string('status', 30)->nullable();
            $table->boolean('is_spv_rejected')->nullable();
            $table->string('spv_reject_reason', 255)->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
