<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AcademicPeriodSeeder::class, // Primero los periodos académicos
            AcademicProgramSeeder::class,
            UserSeeder::class,
            CourseSeeder::class,
            MaterialSeeder::class,
            SectionSeeder::class, // Este depende de AcademicPeriod para crear actividades
        ]);
    }
}
