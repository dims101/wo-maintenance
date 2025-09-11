<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Mat;

class OrderMatSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Daftar Order Type (jenis order)
        $orders = [
            ['type' => 'Repair', 'desc' => 'Perbaikan kerusakan'],
            ['type' => 'Inspection', 'desc' => 'Inspeksi rutin'],
            ['type' => 'Replacement', 'desc' => 'Penggantian komponen'],
            ['type' => 'Maintenance', 'desc' => 'Pemeliharaan umum'],
        ];

        foreach ($orders as $orderData) {
            $order = Order::firstOrCreate(
                ['type' => $orderData['type']],
                ['desc' => $orderData['desc']]
            );

            // 🔹 Tambah Maintenance Activity Type (MAT) sesuai order
            switch ($orderData['type']) {
                case 'Repair':
                    $mats = [
                        ['name' => 'Corrective', 'mat_desc' => 'Perbaikan akibat kerusakan'],
                        ['name' => 'Emergency Repair', 'mat_desc' => 'Perbaikan darurat'],
                    ];
                    break;

                case 'Inspection':
                    $mats = [
                        ['name' => 'Visual Check', 'mat_desc' => 'Pemeriksaan visual'],
                        ['name' => 'Functional Test', 'mat_desc' => 'Tes fungsi peralatan'],
                    ];
                    break;

                case 'Replacement':
                    $mats = [
                        ['name' => 'Component Change', 'mat_desc' => 'Penggantian komponen'],
                        ['name' => 'Part Renewal', 'mat_desc' => 'Peremajaan sparepart'],
                    ];
                    break;

                default: // Maintenance
                    $mats = [
                        ['name' => 'Preventive', 'mat_desc' => 'Pemeliharaan pencegahan'],
                        ['name' => 'Predictive', 'mat_desc' => 'Pemeliharaan prediktif'],
                    ];
                    break;
            }

            foreach ($mats as $matData) {
                Mat::firstOrCreate(
                    [
                        'order_type_id' => $order->id,
                        'name' => $matData['name'],
                    ],
                    ['mat_desc' => $matData['mat_desc']]
                );
            }
        }
    }
}
