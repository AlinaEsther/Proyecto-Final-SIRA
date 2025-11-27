<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use Illuminate\Database\Seeder;

class AcademicPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periods = [
            // Cuatrimestres 2024
            [
                'code' => '2024-C1',
                'name' => 'Primer Cuatrimestre 2024',
                'type' => 'cuatrimestre',
                'year' => 2024,
                'number' => 1,
                'start_date' => '2024-01-15',
                'end_date' => '2024-04-30',
                'is_active' => false,
                'status' => 'completed',
            ],
            [
                'code' => '2024-C2',
                'name' => 'Segundo Cuatrimestre 2024',
                'type' => 'cuatrimestre',
                'year' => 2024,
                'number' => 2,
                'start_date' => '2024-05-15',
                'end_date' => '2024-08-30',
                'is_active' => false,
                'status' => 'completed',
            ],
            [
                'code' => '2024-C3',
                'name' => 'Tercer Cuatrimestre 2024',
                'type' => 'cuatrimestre',
                'year' => 2024,
                'number' => 3,
                'start_date' => '2024-09-15',
                'end_date' => '2024-12-20',
                'is_active' => false,
                'status' => 'completed',
            ],

            // Cuatrimestres 2025
            [
                'code' => '2025-C1',
                'name' => 'Primer Cuatrimestre 2025',
                'type' => 'cuatrimestre',
                'year' => 2025,
                'number' => 1,
                'start_date' => '2025-01-15',
                'end_date' => '2025-04-30',
                'enrollment_start_date' => '2024-12-01',
                'enrollment_end_date' => '2025-01-10',
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'code' => '2025-C2',
                'name' => 'Segundo Cuatrimestre 2025',
                'type' => 'cuatrimestre',
                'year' => 2025,
                'number' => 2,
                'start_date' => '2025-05-15',
                'end_date' => '2025-08-30',
                'is_active' => false,
                'status' => 'upcoming',
            ],
            [
                'code' => '2025-C3',
                'name' => 'Tercer Cuatrimestre 2025',
                'type' => 'cuatrimestre',
                'year' => 2025,
                'number' => 3,
                'start_date' => '2025-09-15',
                'end_date' => '2025-12-20',
                'is_active' => false,
                'status' => 'upcoming',
            ],

            // Semestres 2025 (para programas que usen semestres)
            [
                'code' => '2025-S1',
                'name' => 'Primer Semestre 2025',
                'type' => 'semestre',
                'year' => 2025,
                'number' => 1,
                'start_date' => '2025-01-15',
                'end_date' => '2025-06-30',
                'is_active' => false,
                'status' => 'active',
            ],
            [
                'code' => '2025-S2',
                'name' => 'Segundo Semestre 2025',
                'type' => 'semestre',
                'year' => 2025,
                'number' => 2,
                'start_date' => '2025-08-01',
                'end_date' => '2025-12-20',
                'is_active' => false,
                'status' => 'upcoming',
            ],
        ];

        foreach ($periods as $period) {
            AcademicPeriod::create($period);
        }

        $this->command->info('Periodos académicos creados exitosamente.');
    }
}
