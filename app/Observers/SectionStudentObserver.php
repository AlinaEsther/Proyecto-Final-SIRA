<?php

namespace App\Observers;

use App\Models\SectionStudent;
use App\Models\Section;
use Illuminate\Support\Facades\Cache;

class SectionStudentObserver
{
    /**
     * Handle the SectionStudent "saving" event.
     * Calcula automáticamente el letter_grade cuando se actualiza total_grade
     */
    public function saving(SectionStudent $sectionStudent): void
    {
        // Si total_grade cambió o se está estableciendo, calcular letter_grade
        if ($sectionStudent->isDirty('total_grade')) {
            $sectionStudent->letter_grade = Section::calculateLetterGrade($sectionStudent->total_grade);
        }
    }

    /**
     * Handle the SectionStudent "updating" event.
     * Calcula automáticamente el letter_grade cuando se actualiza total_grade
     */
    public function updating(SectionStudent $sectionStudent): void
    {
        // Este método se ejecuta antes de guardar en una actualización
        if ($sectionStudent->isDirty('total_grade')) {
            $sectionStudent->letter_grade = Section::calculateLetterGrade($sectionStudent->total_grade);
        }
    }

    /**
     * Handle the SectionStudent "created" event.
     */
    public function created(SectionStudent $sectionStudent): void
    {
        // Invalidar cache del profesor cuando se inscribe un estudiante
        $section = $sectionStudent->section;
        if ($section && $section->professor_id) {
            Cache::forget('sections_professor_' . $section->professor_id);
        }
        Cache::forget('all_sections_admin');
    }

    /**
     * Handle the SectionStudent "deleted" event.
     */
    public function deleted(SectionStudent $sectionStudent): void
    {
        // Invalidar cache del profesor cuando se elimina un estudiante
        $section = $sectionStudent->section;
        if ($section && $section->professor_id) {
            Cache::forget('sections_professor_' . $section->professor_id);
        }
        Cache::forget('all_sections_admin');
    }
}
