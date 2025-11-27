<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursePrerequisite extends Model
{
    protected $fillable = [
        'course_id',
        'prerequisite_course_id',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    // Relación con el curso que tiene prerrequisitos
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Relación con el curso que es prerrequisito
    public function prerequisiteCourse()
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }
}
