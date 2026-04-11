<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'schedule_id',
        'classroom_id',
        'status',
        'scanned_at',
        'delay_minutes',
        'scan_latitude',
        'scan_longitude',
        'gps_valid',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'gps_valid' => 'boolean',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'present' => __('attendance.present'),
            'late' => __('attendance.late'),
            'wrong_classroom' => __('attendance.wrong_classroom'),
            'no_lecture' => __('attendance.no_lecture'),
            'missed' => __('attendance.missed'),
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'present' => 'green',
            'late' => 'yellow',
            'wrong_classroom' => 'red',
            'no_lecture' => 'gray',
            'missed' => 'red',
            default => 'gray',
        };
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scanned_at', today());
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
