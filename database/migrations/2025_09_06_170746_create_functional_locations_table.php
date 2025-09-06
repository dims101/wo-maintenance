<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('functional_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resources_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->string('name', 255)->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('functional_locations');
    }
};
