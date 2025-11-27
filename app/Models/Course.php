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

    // Prerrequisitos de este curso (cursos que debo aprobar antes)
    public function prerequisites()
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisites',
            'course_id',
            'prerequisite_course_id'
        )->withPivot('is_mandatory')->withTimestamps();
    }

    // Cursos que tienen a este curso como prerrequisito
    public function requiredBy()
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisites',
            'prerequisite_course_id',
            'course_id'
        )->withPivot('is_mandatory')->withTimestamps();
    }

    // Verificar si el estudiante cumple con los prerrequisitos
    public function studentMeetsPrerequisites($studentId): bool
    {
        $prerequisites = $this->prerequisites()->wherePivot('is_mandatory', true)->get();

        if ($prerequisites->isEmpty()) {
            return true; // No tiene prerrequisitos obligatorios
        }

        // Obtener cursos aprobados por el estudiante
        $approvedCourses = \DB::table('section_student')
            ->join('sections', 'section_student.section_id', '=', 'sections.id')
            ->where('section_student.student_id', $studentId)
            ->where('section_student.status', 'completed')
            ->whereIn('section_student.letter_grade', ['A', 'B', 'C']) // Aprobado
            ->pluck('sections.course_id')
            ->toArray();

        // Verificar que todos los prerrequisitos obligatorios estén aprobados
        foreach ($prerequisites as $prerequisite) {
            if (!in_array($prerequisite->id, $approvedCourses)) {
                return false;
            }
        }

        return true;
    }
}
