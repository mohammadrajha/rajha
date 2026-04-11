<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'instructor_id',
        'classroom_id',
        'course_name',
        'course_name_ar',
        'course_code',
        'day_of_week',
        'start_time',
        'end_time',
        'semester',
        'academic_year',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function isNow(): bool
    {
        $now = Carbon::now();

        if ($now->dayOfWeek !== $this->day_of_week) {
            return false;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $now->between($start, $end);
    }

    public function delayMinutes(): int
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start_time);

        if ($now->greaterThan($start)) {
            return (int) $now->diffInMinutes($start);
        }

        return 0;
    }

    public function localizedCourseName(): string
    {
        return app()->getLocale() === 'ar' && $this->course_name_ar
            ? $this->course_name_ar
            : $this->course_name;
    }

    public static function dayName(int $day): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$day] ?? '';
    }
}
