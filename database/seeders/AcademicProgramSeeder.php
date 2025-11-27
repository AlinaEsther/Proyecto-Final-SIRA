<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use Illuminate\Database\Seeder;

class AcademicProgramSeeder extends Seeder
{
    public function run(): void
    {
        AcademicProgram::create([
            'name' => 'Ingeniería de Software',
            'code' => 'ISW',
            'period_type' => 'cuatrimestre', // Modalidad cuatrimestral
            'description' => 'Programa de Ingeniería en Desarrollo de Software',
            'total_credits' => 240,
            'total_semesters' => 8,
            'status' => 'active',
        ]);

        AcademicProgram::create([
            'name' => 'Ingeniería de Sistemas',
            'code' => 'ISI',
            'period_type' => 'semestre', // Modalidad semestral
            'description' => 'Programa de Ingeniería en Sistemas de Información',
            'total_credits' => 240,
            'total_semesters' => 8,
            'status' => 'active',
        ]);
    }
}
