<?php

namespace Database\Seeders;

use App\Models\Mat;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class MatsSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/csv/mats.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            Mat::updateOrCreate(
                ['id' => $record['id']],
                [
                    'order_type_id' => $record['order_type_id'],
                    'name' => $record['name'],
                    'mat_desc' => $record['mat_desc'],
                ]
            );
        }
    }
}
