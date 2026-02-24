<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'approval_id',
        'user_id',
        'is_pic',
        'pm_id',
        'is_active',
        'start_date',
        'finish_date',
        'duration',
        'week_number',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'is_pic' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'start_date' => 'date',
            'finish_date' => 'date',
            'duration' => 'integer',
            'week_number' => 'integer',
            'year' => 'integer',
        ];
    }

    public function approval()
    {
        return $this->belongsTo(MaintenanceApproval::class, 'approval_id');
    }

    public function preventiveMaintenance()
    {
        return $this->belongsTo(PreventiveMaintenance::class, 'pm_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
