<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceApproval extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wo_id',
        'mat_id',
        'start',
        'finish',
        'progress',
        'is_closed',
        'is_rejected',
        'reject_reason',
        'is_received',
        'delay_reason',
    ];

    protected function casts(): array
    {
        return [
            'start' => 'datetime',
            'finish' => 'datetime',
            'is_closed' => 'boolean',
            'is_rejected' => 'boolean',
            'is_received' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // 🔹 Relasi ke WorkOrder
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'wo_id');
    }

    // 🔹 Relasi ke Mat (jika tabel mats ada)
    public function mat()
    {
        return $this->belongsTo(Mat::class, 'mat_id');
    }

    public function teamAssignments()
    {
        return $this->hasMany(TeamAssignment::class, 'approval_id', 'id');
    }
}
