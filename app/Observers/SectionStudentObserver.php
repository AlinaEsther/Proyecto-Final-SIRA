<?php

namespace App\Observers;

use App\Models\SectionStudent;
use App\Models\Section;

class SectionStudentObserver
{
    /**
     * Handle the SectionStudent "saving" event.
     * Calcula automáticamente el letter_grade cuando se actualiza final_grade
     */
    public function saving(SectionStudent $sectionStudent): void
    {
        // Si final_grade cambió o se está estableciendo, calcular letter_grade
        if ($sectionStudent->isDirty('final_grade')) {
            $sectionStudent->letter_grade = Section::calculateLetterGrade($sectionStudent->final_grade);
        }
    }

    /**
     * Handle the SectionStudent "updating" event.
     * Calcula automáticamente el letter_grade cuando se actualiza final_grade
     */
    public function updating(SectionStudent $sectionStudent): void
    {
        // Este método se ejecuta antes de guardar en una actualización
        if ($sectionStudent->isDirty('final_grade')) {
            $sectionStudent->letter_grade = Section::calculateLetterGrade($sectionStudent->final_grade);
        }
    }
}
