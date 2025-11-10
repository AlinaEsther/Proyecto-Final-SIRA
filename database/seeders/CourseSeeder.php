<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'academic_program_id' => 1,
                'name' => 'Programación I',
                'code' => 'PROG101',
                'description' => 'Introducción a la programación',
                'credits' => 4,
                'semester' => 1,
            ],
            [
                'academic_program_id' => 1,
                'name' => 'Estructura de Datos',
                'code' => 'PROG201',
                'description' => 'Estructuras de datos y algoritmos',
                'credits' => 4,
                'semester' => 2,
            ],
            [
                'academic_program_id' => 1,
                'name' => 'Base de Datos',
                'code' => 'BD301',
                'description' => 'Diseño y gestión de bases de datos',
                'credits' => 3,
                'semester' => 3,
            ],
            [
                'academic_program_id' => 1,
                'name' => 'Desarrollo Web',
                'code' => 'WEB401',
                'description' => 'Desarrollo de aplicaciones web',
                'credits' => 4,
                'semester' => 4,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
