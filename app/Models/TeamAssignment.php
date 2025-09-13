<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'approval_id',
        'user_id',
        'is_pic',
    ];

    protected function casts(): array
    {
        return [
            'is_pic' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function approval()
    {
        return $this->belongsTo(MaintenanceApproval::class, 'approval_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
