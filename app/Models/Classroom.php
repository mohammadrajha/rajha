<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'building',
        'floor',
        'capacity',
        'qr_token',
        'latitude',
        'longitude',
        'gps_radius_meters',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Classroom $classroom) {
            if (empty($classroom->qr_token)) {
                $classroom->qr_token = Str::random(64);
            }
        });
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function regenerateToken(): void
    {
        $this->update(['qr_token' => Str::random(64)]);
    }

    public function getQrPayload(): string
    {
        return json_encode([
            'classroom_id' => $this->id,
            'token' => $this->qr_token,
        ]);
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar
            ? $this->name_ar
            : $this->name;
    }

    public function fullName(): string
    {
        $parts = [$this->localizedName()];
        if ($this->building) {
            $parts[] = $this->building;
        }
        if ($this->floor) {
            $parts[] = $this->floor;
        }
        return implode(' - ', $parts);
    }
}
