<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Department;
use App\Models\PlannerGroup;
use App\Models\RoleAssignment;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Relasi ke Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relasi ke Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    /**
     * Relasi ke Planner Group
     */
    public function plannerGroup()
    {
        return $this->belongsTo(PlannerGroup::class, 'planner_group_id');
    }


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nup',
        'name',
        'company',
        'email',
        'password',
        'avatar',
        'azure_id',
        'dept_id',
        'username',
        'role_id',
        'planner_group_id',
        'section',
        'is_rejected',
        'status',
        'reason',
        'is_defaul_password', // ⚠️ ikut nama kolom di migration
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_rejected' => 'boolean',
            'is_defaul_password' => 'boolean', // ⚠️ konsisten dengan migration
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
