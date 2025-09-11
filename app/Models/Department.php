<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'pic_id',
        'spv_id',
        'manager_id',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relasi ke User sebagai PIC
     */
    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * Relasi ke User sebagai SPV
     */
    public function spv()
    {
        return $this->belongsTo(User::class, 'spv_id');
    }

    /**
     * Relasi ke User sebagai Manager
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Relasi ke banyak User yang berada di department ini
     */
    public function users()
    {
        return $this->hasMany(User::class, 'dept_id');
    }
}