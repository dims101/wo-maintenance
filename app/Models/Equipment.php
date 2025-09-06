<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'equipments';
    protected $fillable = ['func_loc_id', 'name'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function functionalLocation()
    {
        return $this->belongsTo(FunctionalLocation::class, 'func_loc_id');
    }
}
