<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparepartList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wo_id',
        'barcode',
        'qty',
        'uom',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'wo_id');
    }

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class, 'barcode', 'barcode');
    }
}
