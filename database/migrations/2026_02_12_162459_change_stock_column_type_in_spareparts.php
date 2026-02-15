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
            ALTER TABLE spareparts 
            ALTER COLUMN stock TYPE NUMERIC(10,2) 
            USING CASE 
                WHEN stock IS NULL OR stock = '' THEN 0
                ELSE stock::NUMERIC(10,2)
            END
        ");

        // Set default value
        \DB::statement('ALTER TABLE spareparts ALTER COLUMN stock SET DEFAULT 0');

        // Set not null
        \DB::statement('ALTER TABLE spareparts ALTER COLUMN stock SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke varchar
        \DB::statement('
            ALTER TABLE spareparts 
            ALTER COLUMN stock TYPE VARCHAR(255) 
            USING stock::VARCHAR(255)
        ');

        // Drop default
        \DB::statement('ALTER TABLE spareparts ALTER COLUMN stock DROP DEFAULT');

        // Allow null
        \DB::statement('ALTER TABLE spareparts ALTER COLUMN stock DROP NOT NULL');
    }
};
