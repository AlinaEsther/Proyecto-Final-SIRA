<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicProgram extends Model
{
    protected $fillable = [
        'name',
        'code',
        'coordinator_id',
        'period_type',
        'description',
        'total_credits',
        'total_semesters',
        'status',
    ];

    // Coordinador del programa académico
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class)->orderBy('semester');
    }

    public function students()
    {
        return $this->hasMany(Person::class)->whereHas('user', function ($query) {
            $query->where('role', 'student');
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCuatrimestre($query)
    {
        return $query->where('period_type', 'cuatrimestre');
    }

    public function scopeBySemestre($query)
    {
        return $query->where('period_type', 'semestre');
    }
}
