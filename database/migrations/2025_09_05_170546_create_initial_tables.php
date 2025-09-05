<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 15);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->nullable();
            $table->unsignedBigInteger('spv_id')->nullable();
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // planner_groups
        Schema::create('planner_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 15)->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nup', 50)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('company', 100)->nullable();
            $table->string('email', 50)->nullable()->unique();
            $table->string('password', 255)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('azure_id', 255)->nullable();

            $table->unsignedBigInteger('dept_id')->nullable();
            $table->string('username', 20)->nullable()->unique();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('planner_group_id')->nullable();

            $table->string('section', 15)->nullable();
            $table->boolean('is_rejected')->nullable();
            $table->string('status', 30)->nullable();
            $table->string('reject_reason', 255)->nullable();
            $table->boolean('is_defaul_password')->default(true);

            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();

            // foreign keys
            $table->foreign('dept_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            $table->foreign('planner_group_id')->references('id')->on('planner_groups')->nullOnDelete();
        });

        // role_assignments
        Schema::create('role_assignments', function (Blueprint $table) {
            $table->id()->unique(); // increments + unique
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_assignments');
        Schema::dropIfExists('users');
        Schema::dropIfExists('planner_groups');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('roles');
    }
};
