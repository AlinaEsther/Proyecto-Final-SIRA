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
        // Obtener todos los estudiantes
        $students = User::whereHas('person', function($q) {
            $q->whereNotNull('academic_program_id');
        })->get();

        if ($students->isEmpty()) {
            $this->command->warn('No hay estudiantes para inscribir en secciones.');
            return;
        }

        // Obtener todos los cursos
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->warn('No hay cursos para crear secciones.');
            return;
        }

        // Obtener todos los profesores
        $professors = User::whereHas('roles', fn($q) => $q->where('name', 'Profesor'))->get();

        if ($professors->isEmpty()) {
            $this->command->warn('No hay profesores para asignar a secciones.');
            return;
        }

        // Buscar cursos específicos para crear múltiples secciones
        $baseDatos = Course::where('name', 'like', '%Base de Datos%')->first();
        $programacion = Course::where('name', 'like', '%Programación%')->first();

        $schedules = [
            ['days' => ['Lunes', 'Miércoles', 'Viernes'], 'time' => '08:00 AM - 10:00 AM'],
            ['days' => ['Martes', 'Jueves'], 'time' => '10:00 AM - 12:00 PM'],
            ['days' => ['Lunes', 'Miércoles'], 'time' => '02:00 PM - 04:00 PM'],
            ['days' => ['Martes', 'Jueves'], 'time' => '04:00 PM - 06:00 PM'],
        ];

        // Crear 2 secciones para Base de Datos y Programación (cursos principales)
        if ($baseDatos) {
            $this->createSectionsForCourse($baseDatos, $professors, $students, $schedules, '2025-1', 2);
        }

        if ($programacion) {
            $this->createSectionsForCourse($programacion, $professors, $students, $schedules, '2025-1', 2);
        }

        // Crear 1 sección para otros cursos
        $otrosCursos = Course::all()->filter(function($course) use ($baseDatos, $programacion) {
            if ($baseDatos && $course->id === $baseDatos->id) return false;
            if ($programacion && $course->id === $programacion->id) return false;
            return true;
        });

        foreach ($otrosCursos as $course) {
            $this->createSectionsForCourse($course, $professors, $students, $schedules, '2025-1', 1);
        }

        $this->command->info('✅ Todas las secciones creadas con estudiantes y materiales.');
    }

    private function createSectionsForCourse($course, $professors, $students, $schedules, $period, $numberOfSections = 2): void
    {
        $sectionNames = ['A', 'B', 'C', 'D'];

        // Limitar el número de secciones a crear
        $sectionsToCreate = min($numberOfSections, count($sectionNames));

        for ($i = 0; $i < $sectionsToCreate; $i++) {
            $name = $sectionNames[$i];
            $index = $i;
            $professor = $professors->get($index % $professors->count());
            if (!$professor) continue;

            $schedule = $schedules[$index % count($schedules)];

            $section = Section::create([
                'course_id' => $course->id,
                'professor_id' => $professor->id,
                'name' => 'Sección ' . $name,
                'academic_period' => $period,
                'schedule' => $schedule,
                'max_students' => 30,
                'status' => 'open',
            ]);

            // Inscribir 9-12 estudiantes por sección (más estudiantes)
            $numberOfStudents = rand(9, min(12, $students->count()));
            $availableStudents = $students->shuffle();
            $enrolledStudents = collect();

            // Evitar duplicados en el mismo curso
            foreach ($availableStudents as $student) {
                if ($enrolledStudents->count() >= $numberOfStudents) break;

                $alreadyEnrolled = Section::where('course_id', $course->id)
                    ->where('academic_period', $period)
                    ->whereHas('students', fn($q) => $q->where('student_id', $student->id))
                    ->exists();

                if (!$alreadyEnrolled) {
                    $enrolledStudents->push($student);
                }
            }

            foreach ($enrolledStudents as $student) {
                $section->students()->attach($student->id, [
                    'enrollment_date' => now()->subMonths(rand(1, 3)),
                    'status' => 'enrolled',
                    'absences' => rand(0, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->attachMaterialsToSection($section);
            $this->command->info("Sección creada: {$course->code} - {$section->name} ({$professor->name}) con {$enrolledStudents->count()} estudiantes");
        }
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
}
