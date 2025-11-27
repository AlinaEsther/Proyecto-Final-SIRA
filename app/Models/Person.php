<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'card_id',
        'phone',
        'gender',
        'profile_picture',
        'academic_program_id',
        'current_semester',
        'enrollment_date',
        'department',
        'enrollment_number',
        'date_of_birth',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'date_of_birth' => 'date',
    ];

    // Incluir accessors en la serialización JSON/Inertia
    protected $appends = ['full_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
