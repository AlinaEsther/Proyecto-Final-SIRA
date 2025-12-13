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
        'assignments_avg',
        'grade_p1',
        'grade_p2',
        'grade_final',
        'total_grade',
        'letter_grade',
        'absences',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'assignments_avg' => 'decimal:2',
        'grade_p1' => 'decimal:2',
        'grade_p2' => 'decimal:2',
        'grade_final' => 'decimal:2',
        'total_grade' => 'decimal:2',
        'absences' => 'integer',
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
