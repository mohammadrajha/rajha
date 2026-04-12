<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomSchedule extends Model
{
    protected $fillable = [
        'room_no',
        'day',
        'start_time',
        'end_time',
        'semester',
        'dept_no',
        'course_name',
        'instructor_name',
    ];

    protected $casts = [
        'room_no' => 'integer',
        'semester' => 'integer',
        'dept_no' => 'integer',
    ];

    public function departmentEmail(): BelongsTo
    {
        return $this->belongsTo(DepartmentEmail::class, 'dept_no', 'dept_no');
    }

    public function scopeCurrentSemester($query)
    {
        return $query->where('semester', 20252);
    }

    public function scopeForRoom($query, int $roomNo)
    {
        return $query->where('room_no', $roomNo);
    }

    public function scopeForInstructor($query, string $instructorName)
    {
        return $query->where('instructor_name', $instructorName);
    }
}
