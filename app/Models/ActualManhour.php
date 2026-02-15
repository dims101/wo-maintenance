<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActualManhour extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'actual_manhours';

    protected $fillable = [
        'user_id',
        'wo_id',
        'shift',
        'date',
        'start_job',
        'stop_job',
        'actual_time',
        'pm_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_job' => 'datetime',
            'stop_job' => 'datetime',
            'actual_time' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // ✅ BOOT METHOD - Jalankan sebelum save
    protected static function boot()
    {
        parent::boot();

        // Event saat creating (insert baru)
        static::creating(function ($manhour) {
            if ($manhour->start_job) {
                $manhour->calculateShiftAndDate($manhour->start_job);
            }
        });

        // Event saat updating (update existing)
        static::updating(function ($manhour) {
            // Jika stop_job diisi, hitung actual_time
            if ($manhour->isDirty('stop_job') && $manhour->stop_job) {
                $manhour->calculateActualTime();
            }

            // Jika start_job berubah, recalculate shift dan date
            if ($manhour->isDirty('start_job') && $manhour->start_job) {
                $manhour->calculateShiftAndDate($manhour->start_job);
            }
        });
    }

    // ✅ Method untuk hitung shift dan date
    private function calculateShiftAndDate($startJob)
    {
        $start = $startJob instanceof Carbon
            ? $startJob->copy()
            : Carbon::parse($startJob);

        $start->setTimezone(config('app.timezone'));

        $hour = (int) $start->format('H');

        if ($hour >= 7 && $hour <= 14) {
            $this->shift = 1;
            $this->date = $start->toDateString();

        } elseif ($hour >= 15 && $hour <= 22) {
            $this->shift = 2;
            $this->date = $start->toDateString();

        } elseif ($hour >= 23) {
            // 23:00 - 23:59 tetap tanggal hari itu
            $this->shift = 3;
            $this->date = $start->toDateString();

        } else {
            // 00:00 - 06:59 dianggap shift malam hari sebelumnya
            $this->shift = 3;
            $this->date = $start->copy()->subDay()->toDateString();
        }
    }

    // ✅ Method untuk hitung actual_time
    private function calculateActualTime()
    {
        if ($this->start_job && $this->stop_job) {
            $start = Carbon::parse($this->start_job);
            $stop = Carbon::parse($this->stop_job);
            $this->actual_time = (int) round($start->diffInMinutes($stop));
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'wo_id');
    }

    public function preventiveMaintenance()
    {
        return $this->belongsTo(PreventiveMaintenance::class, 'pm_id');
    }
}
