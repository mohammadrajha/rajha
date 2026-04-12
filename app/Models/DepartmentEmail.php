<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentEmail extends Model
{
    protected $fillable = [
        'dept_no',
        'dept_name',
        'head_email',
        'head_name',
    ];

    protected $casts = [
        'dept_no' => 'integer',
    ];

    public function roomSchedules(): HasMany
    {
        return $this->hasMany(RoomSchedule::class, 'dept_no', 'dept_no');
    }
}
