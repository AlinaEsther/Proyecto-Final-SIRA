<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'academic_program_id',
        'name',
        'code',
        'description',
        'credits',
        'semester',
        'status',
    ];

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function materials()
    {
        return $this->morphToMany(Material::class, 'materialable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }
}
