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

        // Crear secciones
        foreach ($courses as $index => $course) {
            $professor = $professors->random();

            $section = Section::create([
                'course_id' => $course->id,
                'professor_id' => $professor->id,
                'name' => 'Sección ' . chr(65 + $index), // A, B, C, etc.
                'academic_period' => '2025-1',
                'schedule' => [
                    'days' => ['Lunes', 'Miércoles', 'Viernes'],
                    'time' => '08:00 AM - 10:00 AM'
                ],
                'max_students' => 30,
                'status' => 'open',
            ]);

            // Inscribir estudiantes con calificaciones
            foreach ($students as $student) {
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
                    'enrollment_date' => now()->subMonths(2),
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
        }

        $this->command->info('Secciones creadas con estudiantes, calificaciones, actividades y materiales.');
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
        $periods = ['p1', 'p2', 'p3', 'exam'];
        $types = ['homework', 'exam', 'project', 'quiz'];

        foreach ($periods as $period) {
            for ($i = 1; $i <= 3; $i++) {
                $type = $period === 'exam' ? 'exam' : $types[array_rand($types)];

                $activity = Activity::create([
                    'section_id' => $section->id,
                    'title' => ucfirst($type) . " $i - Periodo " . strtoupper($period),
                    'description' => "Descripción de la actividad $i del periodo " . strtoupper($period),
                    'type' => $type,
                    'period' => $period,
                    'max_points' => 100,
                    'due_date' => now()->addDays(rand(1, 30)),
                    'status' => 'published',
                ]);

                // Asignar calificaciones a algunos estudiantes
                $students = $section->students->random(min(2, $section->students->count()));

                foreach ($students as $student) {
                    Grade::create([
                        'activity_id' => $activity->id,
                        'student_id' => $student->id,
                        'points_earned' => rand(70, 100),
                        'feedback' => 'Buen trabajo, sigue así.',
                        'graded_at' => now()->subDays(rand(1, 10)),
                    ]);
                }
            }
        }
    }
}
