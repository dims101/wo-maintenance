<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'notification_number',
        'notification_date',
        'priority',
        'work_desc',
        'malfunction_start',
        'equipment_id',
        'planner_group_id',
        'is_breakdown',
        'notes',
        'req_dept_id',
        'req_user_id',
        'urgent_level',
        'status',
        'is_spv_rejected',
        'spv_reject_reason',
        'spv_approve_reason',
        'is_sparepart_complete',
        'revision_note',
    ];

    protected function casts(): array
    {
        return [
            'notification_date' => 'datetime',
            'malfunction_start' => 'datetime',
            'is_breakdown' => 'boolean',
            'is_spv_rejected' => 'boolean',
            'is_sparepart_complete' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'req_dept_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'req_user_id');
    }

    public function plannerGroup()
    {
        return $this->belongsTo(PlannerGroup::class, 'planner_group_id');
    }

    public function maintenanceApproval()
    {
        return $this->hasOne(MaintenanceApproval::class, 'wo_id', 'id');
    }
}
