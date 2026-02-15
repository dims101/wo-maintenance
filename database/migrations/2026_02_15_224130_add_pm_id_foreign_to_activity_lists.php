<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek apakah foreign key sudah ada
        $foreignKeyExists = DB::select("
            SELECT constraint_name 
            FROM information_schema.table_constraints 
            WHERE table_name = 'activity_lists' 
            AND constraint_name = 'activity_lists_pm_id_foreign'
            AND constraint_type = 'FOREIGN KEY'
        ");

        // Jika foreign key belum ada, baru dibuat
        if (empty($foreignKeyExists)) {
            Schema::table('activity_lists', function (Blueprint $table) {
                // Cek apakah kolom pm_id sudah ada
                if (! Schema::hasColumn('activity_lists', 'pm_id')) {
                    // Jika kolom pm_id belum ada, buat kolom + foreign key
                    $table->foreignId('pm_id')->nullable()->after('approval_id')->constrained('preventive_maintenances')->onDelete('cascade');
                } else {
                    // Jika kolom pm_id sudah ada, hanya tambah foreign key
                    $table->foreign('pm_id')->references('id')->on('preventive_maintenances')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_lists', function (Blueprint $table) {
            // Drop foreign key jika ada
            if (Schema::hasColumn('activity_lists', 'pm_id')) {
                $table->dropForeign(['pm_id']);
            }
            // Uncomment jika ingin drop kolom juga:
            // $table->dropColumn('pm_id');
        });
    }
};
