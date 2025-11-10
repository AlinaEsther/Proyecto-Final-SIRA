<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicProgram extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'total_credits',
        'total_semesters',
        'status',
    ];

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
}
