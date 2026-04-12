<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'instructor_name',
        'room_no',
        'room_schedule_id',
        'dept_no',
        'course_name',
        'status',
        'scanned_at',
        'delay_minutes',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'room_no' => 'integer',
        'dept_no' => 'integer',
    ];

    public function roomSchedule(): BelongsTo
    {
        return $this->belongsTo(RoomSchedule::class);
    }

    public function departmentEmail(): BelongsTo
    {
        return $this->belongsTo(DepartmentEmail::class, 'dept_no', 'dept_no');
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
            'wrong_classroom', 'missed' => 'red',
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

    public function scopeForInstructor($query, string $name)
    {
        return $query->where('instructor_name', $name);
    }
}
