<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan hanya jika database PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Ambil semua tabel di schema public
        $tables = DB::table('pg_tables')
            ->where('schemaname', 'public')
            ->pluck('tablename');

        foreach ($tables as $table) {
            // Ambil semua kolom bertipe timestamp tanpa zona waktu
            $columns = DB::table('information_schema.columns')
                ->where('table_name', $table)
                ->where('data_type', 'timestamp without time zone')
                ->pluck('column_name');

            foreach ($columns as $column) {
                // Ubah tipe kolom menjadi timestamptz
                DB::statement(
                    "ALTER TABLE \"$table\" ALTER COLUMN \"$column\" TYPE timestamptz USING \"$column\" AT TIME ZONE 'UTC'"
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jalankan hanya jika database PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Ambil semua tabel di schema public
        $tables = DB::table('pg_tables')
            ->where('schemaname', 'public')
            ->pluck('tablename');

        foreach ($tables as $table) {
            // Ambil semua kolom bertipe timestamptz
            $columns = DB::table('information_schema.columns')
                ->where('table_name', $table)
                ->where('data_type', 'timestamp with time zone')
                ->pluck('column_name');

            foreach ($columns as $column) {
                // Ubah tipe kolom menjadi timestamp tanpa zona waktu
                DB::statement(
                    "ALTER TABLE \"$table\" ALTER COLUMN \"$column\" TYPE timestamp WITHOUT time zone USING \"$column\" AT TIME ZONE 'UTC'"
                );
            }
        }
    }
};