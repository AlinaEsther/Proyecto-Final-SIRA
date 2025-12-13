<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Activity;
use App\Models\Grade;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $sections = Section::with('students')->get();

        if ($sections->isEmpty()) {
            $this->command->warn('No hay secciones para crear actividades.');
            return;
        }

        foreach ($sections as $section) {
            $this->createActivitiesForSection($section);
        }

        $this->command->info('✅ Todas las actividades y calificaciones creadas exitosamente.');
    }

    private function createActivitiesForSection(Section $section): void
    {
        // Obtener el periodo académico activo
        $activePeriod = \App\Models\AcademicPeriod::where('is_active', true)->first();

        if (!$activePeriod) {
            $this->command->warn('No hay periodo académico activo. Saltando creación de actividades.');
            return;
        }

        if ($section->students->isEmpty()) {
            $this->command->warn("Sección {$section->name} no tiene estudiantes.");
            return;
        }

        // Periodos: P1, P2, FIN (independiente del tipo de periodo académico)
        $periodCodes = ['P1', 'P2', 'FIN'];

        // Tipos de actividades regulares (para asignaciones)
        $regularActivityTypes = [
            Activity::TYPE_ASSIGNMENT,
            Activity::TYPE_PRACTICE,
            Activity::TYPE_PROJECT,
        ];

        // Clasificar estudiantes en grupos: Excelentes, Promedios, Malos
        [$excellentStudents, $averageStudents, $poorStudents] = $this->classifyStudents($section->students);

        $activityCounter = 1;

        foreach ($periodCodes as $periodIndex => $periodCode) {
            $isFinalPeriod = ($periodCode === 'FIN');

            // Crear actividades regulares (asignaciones) para P1 y P2
            if (!$isFinalPeriod) {
                $numberOfActivities = rand(4, 6);

                for ($i = 1; $i <= $numberOfActivities; $i++) {
                    $type = $regularActivityTypes[array_rand($regularActivityTypes)];

                    $activity = Activity::create([
                        'section_id' => $section->id,
                        'title' => ucfirst($type) . ' ' . $activityCounter++,
                        'description' => "Evaluación de los objetivos de aprendizaje del curso.",
                        'type' => $type,
                        'period' => $periodCode,
                        'max_points' => rand(10, 20),
                        'due_date' => now()->addDays(rand(1, 30)),
                        'status' => 'published',
                    ]);

                    // Asignar calificaciones según el grupo del estudiante
                    $this->assignGrades($activity, $section->students, $excellentStudents, $averageStudents, $poorStudents);
                }
            }

            // Crear EXACTAMENTE UN examen por periodo (P1, P2, FIN)
            $examLabel = $isFinalPeriod ? 'Examen Final' : 'Examen ' . $periodCode;

            $examActivity = Activity::create([
                'section_id' => $section->id,
                'title' => $examLabel,
                'description' => "Evaluación formal de los contenidos del periodo.",
                'type' => Activity::TYPE_EXAM,
                'period' => $periodCode,
                'max_points' => $isFinalPeriod ? rand(20, 30) : rand(15, 20),
                'due_date' => now()->addDays(rand(1, 30)),
                'status' => 'published',
            ]);

            // Asignar calificaciones del examen (más difíciles)
            $this->assignGrades($examActivity, $section->students, $excellentStudents, $averageStudents, $poorStudents, true);
        }

        $this->command->info("  → {$section->course->code} - {$section->name}: " .
                             $excellentStudents->count() . " excelentes, " .
                             $averageStudents->count() . " promedios, " .
                             $poorStudents->count() . " con bajo rendimiento");
    }

    /**
     * Clasifica estudiantes en tres grupos garantizando al menos 1 de cada tipo.
     */
    private function classifyStudents(Collection $students): array
    {
        $shuffled = $students->shuffle();
        $totalStudents = $shuffled->count();

        if ($totalStudents === 0) {
            return [collect(), collect(), collect()];
        }

        // Garantizar al menos 1 estudiante de cada tipo si hay suficientes estudiantes
        if ($totalStudents >= 3) {
            // Distribución equitativa: ~1/3 excelente, ~1/3 promedio (B-C), ~1/3 bajo
            $excellentCount = (int)ceil($totalStudents / 3);
            $averageCount = (int)ceil($totalStudents / 3);
            $poorCount = $totalStudents - $excellentCount - $averageCount;

            // Garantizar al menos 1 de cada tipo
            if ($excellentCount < 1) $excellentCount = 1;
            if ($averageCount < 1) $averageCount = 1;
            if ($poorCount < 1) $poorCount = 1;

            // Ajustar si la suma excede
            $sum = $excellentCount + $averageCount + $poorCount;
            if ($sum > $totalStudents) {
                $averageCount = $totalStudents - $excellentCount - $poorCount;
            }
        } elseif ($totalStudents === 2) {
            // Con 2 estudiantes: 1 excelente, 1 promedio
            $excellentCount = 1;
            $averageCount = 1;
            $poorCount = 0;
        } else {
            // Con 1 estudiante: promedio
            $excellentCount = 0;
            $averageCount = 1;
            $poorCount = 0;
        }

        $excellentStudents = $shuffled->take($excellentCount);
        $averageStudents = $shuffled->slice($excellentCount, $averageCount);
        $poorStudents = $shuffled->slice($excellentCount + $averageCount, $poorCount);

        return [$excellentStudents, $averageStudents, $poorStudents];
    }

    /**
     * Asigna calificaciones a los estudiantes según su grupo.
     */
    private function assignGrades(
        Activity $activity,
        Collection $allStudents,
        Collection $excellentStudents,
        Collection $averageStudents,
        Collection $poorStudents,
        bool $isExam = false
    ): void {
        foreach ($allStudents as $student) {
            $maxPoints = (int) $activity->max_points;

            // Determinar rango según grupo
            if ($excellentStudents->contains('id', $student->id)) {
                // Excelentes: 90-100% (exámenes: 85-100%)
                $minPercent = $isExam ? 0.85 : 0.90;
                $points = rand((int)ceil($maxPoints * $minPercent), $maxPoints);
            } elseif ($averageStudents->contains('id', $student->id)) {
                // Promedios: 70-89% (exámenes: 65-84%)
                $minPercent = $isExam ? 0.65 : 0.70;
                $maxPercent = $isExam ? 0.84 : 0.89;
                $points = rand((int)ceil($maxPoints * $minPercent), (int)ceil($maxPoints * $maxPercent));
            } else {
                // Malos: 50-69% (exámenes: 45-64%)
                $minPercent = $isExam ? 0.45 : 0.50;
                $maxPercent = $isExam ? 0.64 : 0.69;
                $points = rand((int)ceil($maxPoints * $minPercent), (int)ceil($maxPoints * $maxPercent));
            }

            Grade::create([
                'activity_id' => $activity->id,
                'student_id' => $student->id,
                'points_earned' => $points,
                'feedback' => $this->generateFeedback(($points / $maxPoints) * 100),
                'graded_at' => now()->subDays(rand(1, 15)),
            ]);
        }
    }

    /**
     * Genera feedback basado en el porcentaje de puntos.
     */
    private function generateFeedback(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'Excelente trabajo! Dominas completamente el tema.';
        } elseif ($percentage >= 80) {
            return 'Muy buen trabajo. Sigue así.';
        } elseif ($percentage >= 70) {
            return 'Buen trabajo, pero hay espacio para mejorar.';
        } elseif ($percentage >= 60) {
            return 'Debes reforzar estos conceptos. Te recomiendo revisar el material.';
        } else {
            return 'Es necesario que estudies más este tema. Consulta con el profesor.';
        }
    }
}

