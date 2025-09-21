<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'approval_id',
        'task',
        'is_done',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function approval()
    {
        return $this->belongsTo(MaintenanceApproval::class, 'approval_id');
    }
}
