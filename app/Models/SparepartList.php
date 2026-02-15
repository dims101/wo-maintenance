<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparepartList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wo_id',
        'pm_id',
        'barcode',
        'qty',
        'uom',
        'planner_group_id',
        'is_completed',
        'requested_sparepart',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'is_completed' => 'boolean',
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

    public function plannerGroup()
    {
        return $this->belongsTo(PlannerGroup::class, 'planner_group_id');
    }

    // Relasi ke PreventiveMaintenance
    public function preventiveMaintenance()
    {
        return $this->belongsTo(PreventiveMaintenance::class, 'pm_id');
    }
}
