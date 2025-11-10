<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function person()
    {
        return $this->hasOne(Person::class);
    }

    public function sectionsAsStudent()
    {
        return $this->belongsToMany(Section::class, 'section_student', 'student_id', 'section_id')
            ->withPivot([
                'enrollment_date',
                'status',
                'grade_p1',
                'grade_p2',
                'grade_p3',
                'grade_exam',
                'current_grade',
                'final_grade',
                'letter_grade'
            ])
            ->withTimestamps();
    }

    public function sectionsAsProfessor()
    {
        return $this->hasMany(Section::class, 'professor_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'student_id');
    }

    /**
     * Scope a query to only include professors.
     */
    public function scopeProfessors($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'Profesor');
        });
    }

    /**
     * Scope a query to only include students.
     */
    public function scopeStudents($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'Estudiante');
        });
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('person');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    /**
     * Check if user is professor
     */
    public function isProfessor(): bool
    {
        return $this->hasRole('Profesor');
    }

    /**
     * Check if user is student
     */
    public function isStudent(): bool
    {
        return $this->hasRole('Estudiante');
    }

}
