<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Observers\SectionStudentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([SectionStudentObserver::class])]
class SectionStudent extends Pivot
{
    protected $table = 'section_student';

    protected $fillable = [
        'section_id',
        'student_id',
        'enrollment_date',
        'status',
        'grade_p1',
        'grade_p2',
        'grade_p3',
        'grade_exam',
        'current_grade',
        'final_grade',
        'letter_grade',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'grade_p1' => 'decimal:2',
        'grade_p2' => 'decimal:2',
        'grade_p3' => 'decimal:2',
        'grade_exam' => 'decimal:2',
        'current_grade' => 'decimal:2',
        'final_grade' => 'decimal:2',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
