<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'course_id',
        'professor_id',
        'name',
        'academic_period',
        'schedule',
        'max_students',
        'status',
    ];

    protected $casts = [
        'schedule' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'section_student', 'section_id', 'student_id')
            ->using(SectionStudent::class)
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

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function materials()
    {
        return $this->morphToMany(Material::class, 'materialable')
            ->withPivot(['is_required', 'order'])
            ->orderByPivot('order');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('academic_period', $period);
    }

    public function getEnrolledCountAttribute()
    {
        return $this->students()->wherePivot('status', 'enrolled')->count();
    }

    public function getAverageGradeAttribute()
    {
        return $this->students()
            ->wherePivot('status', 'enrolled')
            ->avg('section_student.current_grade');
    }

    /**
     * Calcula el grado literal basado en la calificación numérica
     * A: > 90
     * B: 80-89
     * C: 70-79
     * F: <= 69
     */
    public static function calculateLetterGrade(?float $numericGrade): ?string
    {
        if ($numericGrade === null) {
            return null;
        }

        if ($numericGrade > 90) {
            return 'A';
        } elseif ($numericGrade >= 80) {
            return 'B';
        } elseif ($numericGrade >= 70) {
            return 'C';
        } else {
            return 'F';
        }
    }
}
