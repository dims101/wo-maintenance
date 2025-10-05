<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_maintenances', function (Blueprint $table) {
            $table->id();

            $table->string('order')->nullable();
            $table->string('notification_number')->nullable();
            $table->string('main_workctr')->nullable();
            $table->text('description')->nullable();
            $table->string('system_status')->nullable();

            $table->date('basic_start_date')->nullable();
            $table->time('start_time')->nullable();

            $table->string('plan_total_cost')->nullable();
            $table->string('actual_total_cost')->nullable();

            $table->date('actual_start_date')->nullable();
            $table->time('actual_start_time')->nullable();
            $table->dateTime('actual_finish')->nullable();

            $table->string('entered_by')->nullable();
            $table->string('order_type')->nullable();
            $table->string('user_status')->nullable();

            $table->string('functional_location')->nullable();
            $table->text('fl_desc')->nullable();

            $table->string('equipment')->nullable();
            $table->text('eq_desc')->nullable();

            $table->string('tech_obj_desc')->nullable();
            $table->string('maintenance_plan')->nullable();

            $table->timestamptz('created_at')->nullable();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenances');
    }
};
