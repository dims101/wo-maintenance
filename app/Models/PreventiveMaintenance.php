<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreventiveMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'preventive_maintenances';

    protected $fillable = [
        'order',
        'notification_number',
        'main_workctr',
        'description',
        'system_status',
        'basic_start_date',
        'start_time',
        'plan_total_cost',
        'actual_total_cost',
        'actual_start_date',
        'actual_start_time',
        'actual_finish',
        'entered_by',
        'order_type',
        'user_status',
        'functional_location',
        'fl_desc',
        'equipment',
        'eq_desc',
        'tech_obj_desc',
        'maintenance_plan',
    ];

    protected function casts(): array
    {
        return [
            'basic_start_date' => 'date',
            'start_time' => 'datetime:H:i',
            'actual_start_date' => 'date',
            'actual_start_time' => 'datetime:H:i',
            'actual_finish' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
