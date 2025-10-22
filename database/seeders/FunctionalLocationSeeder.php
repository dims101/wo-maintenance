<?php

namespace Database\Seeders;

use App\Models\FunctionalLocation;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class FunctionalLocationSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/csv/functional_locations.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            FunctionalLocation::updateOrCreate(
                ['id' => $record['id']],
                [
                    'name' => $record['FunctionalLoc'],
                    'resources_id' => $record['resources_id'],
                ]
            );
        }
    }
}
