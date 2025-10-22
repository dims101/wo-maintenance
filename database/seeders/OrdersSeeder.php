<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/csv/orders.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            Order::updateOrCreate(
                ['id' => $record['id']],
                [
                    'type' => $record['type'],
                    'desc' => $record['desc'],
                ]
            );
        }
    }
}
