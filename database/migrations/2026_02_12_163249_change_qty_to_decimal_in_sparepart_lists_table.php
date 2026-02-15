<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Gunakan raw SQL karena perlu USING clause
        \DB::statement("
            ALTER TABLE sparepart_lists 
            ALTER COLUMN qty TYPE NUMERIC(10,0) 
            USING CASE 
                WHEN qty IS NULL OR qty = '' THEN 0
                ELSE qty::NUMERIC(10,2)
            END
        ");

        // Set default value
        \DB::statement('ALTER TABLE sparepart_lists ALTER COLUMN qty SET DEFAULT 0');

        // Set not null
        \DB::statement('ALTER TABLE sparepart_lists ALTER COLUMN qty SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke varchar
        \DB::statement('
            ALTER TABLE sparepart_lists 
            ALTER COLUMN qty TYPE VARCHAR(255) 
            USING qty::VARCHAR(255)
        ');

        // Drop default
        \DB::statement('ALTER TABLE sparepart_lists ALTER COLUMN qty DROP DEFAULT');

        // Allow null
        \DB::statement('ALTER TABLE sparepart_lists ALTER COLUMN qty DROP NOT NULL');
    }
};
