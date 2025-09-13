<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sparepart;

class SparepartSeeder extends Seeder
{
    public function run(): void
    {
        $spareparts = [
            [
                'barcode' => 'SP001',
                'code'    => 'BRG-001',
                'name'    => 'Bearing 6203',
                'stock'   => '50',
                'uom'     => 'pcs',
            ],
            [
                'barcode' => 'SP002',
                'code'    => 'BLT-010',
                'name'    => 'Bolt M10 x 50',
                'stock'   => '200',
                'uom'     => 'pcs',
            ],
            [
                'barcode' => 'SP003',
                'code'    => 'FLT-001',
                'name'    => 'Oil Filter',
                'stock'   => '30',
                'uom'     => 'pcs',
            ],
            [
                'barcode' => 'SP004',
                'code'    => 'OL-001',
                'name'    => 'Lubricant Oil 1L',
                'stock'   => '100',
                'uom'     => 'ltr',
            ],
            [
                'barcode' => 'SP005',
                'code'    => 'SEAL-01',
                'name'    => 'Rubber Seal',
                'stock'   => '150',
                'uom'     => 'pcs',
            ],
        ];

        foreach ($spareparts as $sp) {
            Sparepart::firstOrCreate(
                ['barcode' => $sp['barcode']],
                $sp
            );
        }
    }
}
