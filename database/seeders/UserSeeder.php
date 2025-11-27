<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usersList = [
            [
                'name' => 'Admin SIRA',
                'email' => 'sisadmin@sira.test',
                'password' => Hash::make('admin'),
                'personData' => [
                    'first_name' => 'Admin',
                    'last_name' => 'Sistema',
                    'card_id' => '000-0000000-0',
                    'enrollment_number' => 'AdminSistema-SIS-01',
                    'date_of_birth' => '1985-06-15',
                ],
                'roles' => ['Admin']
            ],

            // PROFESORES
            [
                'name' => 'Prof. Juan Pérez',
                'email' => 'juan.perez@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Juan',
                    'last_name' => 'Pérez',
                    'card_id' => '001-1234567-8',
                    'department' => 'Ingeniería de Software',
                    'enrollment_number' => 'JuanPerez-ISW-01',
                    'date_of_birth' => '1980-03-20',
                ],
                'roles' => ['Profesor']
            ],
            [
                'name' => 'Prof. María González',
                'email' => 'maria.gonzalez@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'María',
                    'last_name' => 'González',
                    'card_id' => '001-2345678-9',
                    'department' => 'Ingeniería de Software',
                    'enrollment_number' => 'MariaGonzalez-ISW-02',
                    'date_of_birth' => '1982-11-08',
                ],
                'roles' => ['Profesor']
            ],
            [
                'name' => 'Prof. Roberto Sánchez',
                'email' => 'roberto.sanchez@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Roberto',
                    'last_name' => 'Sánchez',
                    'card_id' => '001-3456789-0',
                    'department' => 'Matemáticas',
                    'enrollment_number' => 'RobertoSanchez-MAT-01',
                    'date_of_birth' => '1978-07-15',
                ],
                'roles' => ['Profesor']
            ],
            [
                'name' => 'Prof. Carmen Díaz',
                'email' => 'carmen.diaz@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Carmen',
                    'last_name' => 'Díaz',
                    'card_id' => '001-4567890-1',
                    'department' => 'Ciencias de la Computación',
                    'enrollment_number' => 'CarmenDiaz-CC-01',
                    'date_of_birth' => '1985-12-03',
                ],
                'roles' => ['Profesor']
            ],
            [
                'name' => 'Prof. Luis Morales',
                'email' => 'luis.morales@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Luis',
                    'last_name' => 'Morales',
                    'card_id' => '001-5678901-2',
                    'department' => 'Redes y Telecomunicaciones',
                    'enrollment_number' => 'LuisMorales-RT-01',
                    'date_of_birth' => '1983-04-22',
                ],
                'roles' => ['Profesor']
            ],

            // ESTUDIANTES
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Carlos',
                    'last_name' => 'Rodríguez',
                    'card_id' => '002-9876543-2',
                    'academic_program_id' => 1,
                    'current_semester' => 3,
                    'enrollment_date' => now()->subYears(1),
                    'enrollment_number' => '2024-0001',
                    'date_of_birth' => '2002-05-12',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Ana',
                    'last_name' => 'Martínez',
                    'card_id' => '002-8765432-1',
                    'academic_program_id' => 1,
                    'current_semester' => 3,
                    'enrollment_date' => now()->subYears(1),
                    'enrollment_number' => '2024-0002',
                    'date_of_birth' => '2003-09-28',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Pedro López',
                'email' => 'pedro.lopez@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Pedro',
                    'last_name' => 'López',
                    'card_id' => '002-7654321-0',
                    'academic_program_id' => 1,
                    'current_semester' => 2,
                    'enrollment_date' => now()->subMonths(6),
                    'enrollment_number' => '2024-0003',
                    'date_of_birth' => '2003-02-18',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Sofía Ramírez',
                'email' => 'sofia.ramirez@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Sofía',
                    'last_name' => 'Ramírez',
                    'card_id' => '002-6543210-9',
                    'academic_program_id' => 1,
                    'current_semester' => 4,
                    'enrollment_date' => now()->subYears(2),
                    'enrollment_number' => '2024-0004',
                    'date_of_birth' => '2002-11-30',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Diego Fernández',
                'email' => 'diego.fernandez@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Diego',
                    'last_name' => 'Fernández',
                    'card_id' => '002-5432109-8',
                    'academic_program_id' => 1,
                    'current_semester' => 3,
                    'enrollment_date' => now()->subYears(1),
                    'enrollment_number' => '2024-0005',
                    'date_of_birth' => '2003-07-08',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Luisa Torres',
                'email' => 'luisa.torres@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Luisa',
                    'last_name' => 'Torres',
                    'card_id' => '002-4321098-7',
                    'academic_program_id' => 1,
                    'current_semester' => 2,
                    'enrollment_date' => now()->subMonths(6),
                    'enrollment_number' => '2024-0006',
                    'date_of_birth' => '2003-04-25',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Miguel Vargas',
                'email' => 'miguel.vargas@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Miguel',
                    'last_name' => 'Vargas',
                    'card_id' => '002-3210987-6',
                    'academic_program_id' => 1,
                    'current_semester' => 4,
                    'enrollment_date' => now()->subYears(2),
                    'enrollment_number' => '2024-0007',
                    'date_of_birth' => '2002-08-14',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Isabella Cruz',
                'email' => 'isabella.cruz@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Isabella',
                    'last_name' => 'Cruz',
                    'card_id' => '002-2109876-5',
                    'academic_program_id' => 1,
                    'current_semester' => 3,
                    'enrollment_date' => now()->subYears(1),
                    'enrollment_number' => '2024-0008',
                    'date_of_birth' => '2003-01-19',
                ],
                'roles' => ['Estudiante']
            ],
        ];

        foreach ($usersList as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);

            if (isset($userData['personData'])) {
                $personData = $userData['personData'];
                $personData['user_id'] = $user->id;

                Person::create($personData);
            }

            if (isset($userData['roles'])) {
                foreach ($userData['roles'] as $roleName) {
                    $role = Role::where('name', $roleName)->first();
                    if ($role) {
                        $user->assignRole($role);
                    }
                }
            }
        }
    }
}
