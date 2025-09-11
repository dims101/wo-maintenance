<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_type_id',
        'name',
        'mat_desc',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // 🔹 Relasi ke Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_type_id');
    }

    // 🔹 Relasi ke MaintenanceApproval
    // public function maintenanceApprovals()
    // {
    //     return $this->hasMany(MaintenanceApproval::class, 'mat_id');
    // }
}
