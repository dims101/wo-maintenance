<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function actualManhours()
    {
        return $this->hasMany(ActualManhour::class, 'user_id');
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

    /**
     * Get total manhour user hari ini (dalam menit)
     */
    public function getTotalManhourToday()
    {
        return $this->actualManhours()
            ->whereDate('date', today())
            ->sum('actual_time');
    }

    /**
     * Check apakah user punya active assignment (WO atau PM)
     */
    public function hasActiveAssignment()
    {
        // Check Work Order assignment
        $hasWoAssignment = TeamAssignment::where('user_id', $this->id)
            ->whereNotNull('approval_id')
            ->whereHas('approval.workOrder', function ($q) {
                $q->whereNotIn('status', ['Closed', 'Rejected', 'CLOSED', 'REJECTED']);
            })
            ->exists();

        // Check PM assignment
        $hasPmAssignment = TeamAssignment::where('user_id', $this->id)
            ->whereNotNull('pm_id')
            ->whereHas('preventiveMaintenance', function ($q) {
                $q->whereNotIn('user_status', ['COMPLETED', 'CLOSED']);
            })
            ->exists();

        return $hasWoAssignment || $hasPmAssignment;
    }
}
