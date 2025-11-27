<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Course;
use App\Models\User;
use App\Models\Activity;
use App\Models\Grade;
use App\Models\Material;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener profesores y estudiantes
        $professors = User::whereHas('person', function($q) {
            $q->whereNotNull('department');
        })->get();

        $students = User::whereHas('person', function($q) {
            $q->whereNotNull('academic_program_id');
        })->get();

        // Obtener cursos
        $courses = Course::all();

        if ($professors->isEmpty() || $students->isEmpty() || $courses->isEmpty()) {
            $this->command->warn('No hay suficientes profesores, estudiantes o cursos para crear secciones.');
            return;
        }

        $sectionNames = ['A', 'B', 'C'];
        $periods = ['2025-1', '2025-2'];
        $schedules = [
            ['days' => ['Lunes', 'Miércoles', 'Viernes'], 'time' => '08:00 AM - 10:00 AM'],
            ['days' => ['Martes', 'Jueves'], 'time' => '10:00 AM - 12:00 PM'],
            ['days' => ['Lunes', 'Miércoles'], 'time' => '02:00 PM - 04:00 PM'],
            ['days' => ['Martes', 'Jueves'], 'time' => '04:00 PM - 06:00 PM'],
        ];

        // Crear múltiples secciones para cada curso
        foreach ($courses as $course) {
            $sectionsToCreate = rand(1, 2); // 1-2 secciones por curso

            for ($s = 0; $s < $sectionsToCreate; $s++) {
                $professor = $professors->random();
                $schedule = $schedules[array_rand($schedules)];

                $section = Section::create([
                    'course_id' => $course->id,
                    'professor_id' => $professor->id,
                    'name' => 'Sección ' . $sectionNames[$s % count($sectionNames)],
                    'academic_period' => $periods[array_rand($periods)],
                    'schedule' => $schedule,
                    'max_students' => 30,
                    'status' => 'open',
                ]);

                // Inscribir estudiantes aleatorios (entre 4-8 estudiantes por sección)
                $numberOfStudents = rand(4, min(8, $students->count()));
                $enrolledStudents = $students->random($numberOfStudents);

                foreach ($enrolledStudents as $student) {
                    // Generar notas aleatorias para demostración
                    $gradeP1 = rand(65, 100);
                    $gradeP2 = rand(65, 100);
                    $gradeP3 = rand(65, 100);
                    $gradeExam = rand(65, 100);

                    // Calcular nota final (ejemplo: promedio simple)
                    $finalGrade = ($gradeP1 + $gradeP2 + $gradeP3 + $gradeExam) / 4;
                    $currentGrade = ($gradeP1 + $gradeP2 + $gradeP3) / 3;

                    // Calcular letra automáticamente (el Observer se encargará)
                    $letterGrade = Section::calculateLetterGrade($finalGrade);

                    $section->students()->attach($student->id, [
                        'enrollment_date' => now()->subMonths(rand(1, 3)),
                        'status' => 'enrolled',
                        'grade_p1' => $gradeP1,
                        'grade_p2' => $gradeP2,
                        'grade_p3' => $gradeP3,
                        'grade_exam' => $gradeExam,
                        'current_grade' => $currentGrade,
                        'final_grade' => $finalGrade,
                        'letter_grade' => $letterGrade,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Crear algunas actividades para cada periodo
                $this->createActivitiesForSection($section);

                // Asociar materiales a la sección
                $this->attachMaterialsToSection($section);

                $this->command->info("Sección creada: {$course->code} - {$section->name} con {$enrolledStudents->count()} estudiantes");
            }
        }

        $this->command->info('Todas las secciones creadas con estudiantes, calificaciones, actividades y materiales.');
    }

    private function attachMaterialsToSection(Section $section): void
    {
        $materials = Material::all();

        if ($materials->isEmpty()) {
            return;
        }

        // Asociar 2-3 materiales aleatorios a cada sección
        $selectedMaterials = $materials->random(min(rand(2, 3), $materials->count()));

        foreach ($selectedMaterials as $index => $material) {
            $section->materials()->attach($material->id, [
                'is_required' => $index === 0, // El primero es requerido
                'order' => $index + 1,
            ]);
        }
    }

    private function createActivitiesForSection(Section $section): void
    {
        // Obtener el periodo académico activo
        $activePeriod = \App\Models\AcademicPeriod::where('is_active', true)->first();

        if (!$activePeriod) {
            $this->command->warn('No hay periodo académico activo. Saltando creación de actividades.');
            return;
        }

        // Determinar cuántos periodos académicos usar basado en el tipo
        // Cuatrimestre: 2 periodos (C1, C2) + Final
        // Semestre: 3 periodos (S1 incluye 3 parciales) + Final
        $periodType = $activePeriod->type;
        $periodCodes = [];

        if ($periodType === 'cuatrimestre') {
            // Para cuatrimestres usamos 2 periodos + final
            $periodCodes = [$activePeriod->code, $activePeriod->code, $activePeriod->code]; // P1, P2, Final
        } else {
            // Para semestres usamos 3 periodos + final
            $periodCodes = [$activePeriod->code, $activePeriod->code, $activePeriod->code, $activePeriod->code]; // P1, P2, P3, Final
        }

        // Tipos de actividades disponibles (sin examen)
        $regularActivityTypes = [
            Activity::TYPE_ASSIGNMENT,
            Activity::TYPE_PRACTICE,
            Activity::TYPE_PROJECT,
        ];

        $activityCounter = 1;

        foreach ($periodCodes as $periodIndex => $periodCode) {
            $isLastPeriod = ($periodIndex === count($periodCodes) - 1);

            // Crear actividades regulares (no exámenes)
            if (!$isLastPeriod) {
                $numberOfActivities = rand(3, 5);

                for ($i = 1; $i <= $numberOfActivities; $i++) {
                    $type = $regularActivityTypes[array_rand($regularActivityTypes)];

                    $activity = Activity::create([
                        'section_id' => $section->id,
                        'title' => 'Actividad ' . $activityCounter++,
                        'description' => "Evaluación de los objetivos de aprendizaje del curso.",
                        'type' => $type,
                        'period' => $periodCode,
                        'max_points' => rand(5, 20), // Puntos variables según el profesor
                        'due_date' => now()->addDays(rand(1, 30)),
                        'status' => 'published',
                    ]);

                    // Asignar calificaciones a todos los estudiantes
                    foreach ($section->students as $student) {
                        $maxPoints = (int) $activity->max_points;
                        $points = rand((int) ceil($maxPoints * 0.7), $maxPoints); // 70-100% de los puntos

                        Grade::create([
                            'activity_id' => $activity->id,
                            'student_id' => $student->id,
                            'points_earned' => $points,
                            'feedback' => $this->generateFeedback($points / $maxPoints * 100),
                            'graded_at' => now()->subDays(rand(1, 15)),
                        ]);
                    }
                }
            }

            // Crear EXACTAMENTE UN examen por periodo
            $examLabel = $isLastPeriod ? 'Examen Final' : 'Examen P' . ($periodIndex + 1);

            $examActivity = Activity::create([
                'section_id' => $section->id,
                'title' => $examLabel,
                'description' => "Evaluación formal de los contenidos del periodo.",
                'type' => Activity::TYPE_EXAM,
                'period' => $periodCode,
                'max_points' => $isLastPeriod ? rand(15, 25) : rand(10, 15), // Examen final vale más
                'due_date' => now()->addDays(rand(1, 30)),
                'status' => 'published',
            ]);

            // Asignar calificaciones del examen a todos los estudiantes
            foreach ($section->students as $student) {
                $maxPoints = (int) $examActivity->max_points;
                $points = rand((int) ceil($maxPoints * 0.65), $maxPoints); // Exámenes son más difíciles (65-100%)

                Grade::create([
                    'activity_id' => $examActivity->id,
                    'student_id' => $student->id,
                    'points_earned' => $points,
                    'feedback' => $this->generateFeedback($points / $maxPoints * 100),
                    'graded_at' => now()->subDays(rand(1, 15)),
                ]);
            }
        }
    }

    private function generateFeedback(float $points): string
    {
        if ($points >= 90) {
            return 'Excelente trabajo! Dominas completamente el tema.';
        } elseif ($points >= 80) {
            return 'Muy buen trabajo. Sigue así.';
        } elseif ($points >= 70) {
            return 'Buen trabajo, pero hay espacio para mejorar.';
        } else {
            return 'Debes reforzar estos conceptos. Te recomiendo revisar el material.';
        }
    }
}
