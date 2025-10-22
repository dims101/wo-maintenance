<?php

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/csv/resources.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            Resource::updateOrCreate(
                ['id' => $record['id']],
                [
                    'name' => $record['Resource'],
                    'plant_id' => $record['plant_id'],
                ]
            );
        }
    }
}
