<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'year',
        'number',
        'start_date',
        'end_date',
        'enrollment_start_date',
        'enrollment_end_date',
        'is_active',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'enrollment_start_date' => 'date',
        'enrollment_end_date' => 'date',
        'is_active' => 'boolean',
        'year' => 'integer',
        'number' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeEnrollmentOpen($query)
    {
        $today = now()->toDateString();
        return $query->where('enrollment_start_date', '<=', $today)
                     ->where('enrollment_end_date', '>=', $today);
    }

    // Relaciones
    public function sections()
    {
        return $this->hasMany(Section::class, 'academic_period', 'code');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'period', 'code');
    }

    // Helper para verificar si el periodo está en ventana de inscripción
    public function isEnrollmentOpen(): bool
    {
        if (!$this->enrollment_start_date || !$this->enrollment_end_date) {
            return false;
        }

        $today = now()->toDateString();
        return $this->enrollment_start_date <= $today && $this->enrollment_end_date >= $today;
    }
}
