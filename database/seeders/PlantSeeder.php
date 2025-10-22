<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/csv/plants.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            Plant::updateOrCreate(
                ['id' => $record['id']],
                ['name' => $record['Plant']]
            );
        }
    }
}
