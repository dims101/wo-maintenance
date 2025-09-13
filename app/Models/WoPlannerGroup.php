<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WoPlannerGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'approval_id',
        'planner_group_id',
        'status',
        'pg_reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function approval()
    {
        return $this->belongsTo(MaintenanceApproval::class, 'approval_id');
    }

    public function plannerGroup()
    {
        return $this->belongsTo(PlannerGroup::class, 'planner_group_id');
    }
}
