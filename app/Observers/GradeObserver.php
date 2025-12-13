<?php

namespace App\Observers;

use App\Models\Grade;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GradeObserver
{
    /**
     * Se ejecuta cuando se crea o actualiza una calificación.
     * Recalcula current_grade y final_grade automáticamente.
     */
    public function saved(Grade $grade): void
    {
        if ($grade->activity && $grade->activity->section_id) {
            $this->recalculateStudentGrades($grade->student_id, $grade->activity->section_id);
        }
    }

    /**
     * Se ejecuta cuando se elimina una calificación.
     */
    public function deleted(Grade $grade): void
    {
        if ($grade->activity && $grade->activity->section_id) {
            $this->recalculateStudentGrades($grade->student_id, $grade->activity->section_id);
        }
    }

    /**
     * Recalcula las calificaciones de un estudiante en una sección.
     * Calcula por categoría: Asignaciones (assignment, practice, project), P1, P2, Final
     */
    private function recalculateStudentGrades(int $studentId, int $sectionId): void
    {
        // Obtener todas las calificaciones del estudiante en esta sección
        $grades = Grade::whereHas('activity', function($q) use ($sectionId) {
            $q->where('section_id', $sectionId);
        })
        ->where('student_id', $studentId)
        ->with('activity')
        ->get();

        if ($grades->isEmpty()) {
            // Si no hay calificaciones, limpiar los campos
            DB::table('section_student')
                ->where('section_id', $sectionId)
                ->where('student_id', $studentId)
                ->update([
                    'assignments_avg' => null,
                    'grade_p1' => null,
                    'grade_p2' => null,
                    'grade_final' => null,
                    'total_grade' => null,
                    'letter_grade' => null,
                    'updated_at' => now(),
                ]);

            $this->invalidateCache($sectionId);
            return;
        }

        // Separar calificaciones por tipo
        $assignments = collect();
        $p1Exam = null;
        $p2Exam = null;
        $finalExam = null;

        foreach ($grades as $grade) {
            if ($grade->points_earned === null || $grade->activity->max_points <= 0) {
                continue;
            }

            $percentage = ($grade->points_earned / $grade->activity->max_points) * 100;
            $period = $grade->activity->period;
            $type = $grade->activity->type;

            // Asignaciones (assignment, practice, project)
            if (in_array($type, ['assignment', 'practice', 'project'])) {
                $assignments->push($percentage);
            }
            // Exámenes por periodo
            elseif ($type === 'exam') {
                if ($period === 'P1') {
                    $p1Exam = $percentage;
                } elseif ($period === 'P2') {
                    $p2Exam = $percentage;
                } elseif ($period === 'FIN') {
                    $finalExam = $percentage;
                }
            }
        }

        // Calcular promedios
        $assignmentsAvg = $assignments->isNotEmpty() ? round($assignments->average(), 2) : null;
        $gradeP1 = $p1Exam !== null ? round($p1Exam, 2) : null;
        $gradeP2 = $p2Exam !== null ? round($p2Exam, 2) : null;
        $gradeFinal = $finalExam !== null ? round($finalExam, 2) : null;

        // Calcular total ponderado (si todos los componentes existen)
        // Fórmula: Asignaciones 30%, P1 20%, P2 20%, Final 30%
        $totalGrade = null;
        if ($assignmentsAvg !== null && $gradeP1 !== null && $gradeP2 !== null && $gradeFinal !== null) {
            $totalGrade = round(
                ($assignmentsAvg * 0.30) +
                ($gradeP1 * 0.20) +
                ($gradeP2 * 0.20) +
                ($gradeFinal * 0.30),
                2
            );
        }

        $letterGrade = $totalGrade ? Section::calculateLetterGrade($totalGrade) : null;

        // Actualizar en section_student
        DB::table('section_student')
            ->where('section_id', $sectionId)
            ->where('student_id', $studentId)
            ->update([
                'assignments_avg' => $assignmentsAvg,
                'grade_p1' => $gradeP1,
                'grade_p2' => $gradeP2,
                'grade_final' => $gradeFinal,
                'total_grade' => $totalGrade,
                'letter_grade' => $letterGrade,
                'updated_at' => now(),
            ]);

        // Invalidar cache relacionado
        $this->invalidateCache($sectionId);
    }

    /**
     * Invalida el cache relacionado con la sección.
     */
    private function invalidateCache(int $sectionId): void
    {
        // Obtener la sección para invalidar cache del profesor
        $section = Section::find($sectionId);
        if ($section && $section->professor_id) {
            Cache::forget('sections_professor_' . $section->professor_id);
        }

        // Invalidar cache de admin
        Cache::forget('all_sections_admin');
    }
}

