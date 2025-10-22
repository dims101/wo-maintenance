<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/csv/equipments.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            Equipment::updateOrCreate(
                ['id' => $record['id']],
                [
                    'name' => $record['Equipment'],
                    'func_loc_id' => $record['func_loc_id'],
                ]
            );
        }
    }
}
