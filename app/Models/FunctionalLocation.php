<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FunctionalLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['resources_id', 'name'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resources_id');
    }
}
