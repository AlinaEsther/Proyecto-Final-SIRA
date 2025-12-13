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
            [
                'name' => 'Prof. Elena Torres',
                'email' => 'elena.torres@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Elena',
                    'last_name' => 'Torres',
                    'card_id' => '001-6789012-3',
                    'department' => 'Base de Datos',
                    'enrollment_number' => 'ElenaTorres-BD-01',
                    'date_of_birth' => '1981-08-19',
                ],
                'roles' => ['Profesor']
            ],
            [
                'name' => 'Prof. Miguel Ángel Reyes',
                'email' => 'miguel.reyes@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Miguel Ángel',
                    'last_name' => 'Reyes',
                    'card_id' => '001-7890123-4',
                    'department' => 'Programación',
                    'enrollment_number' => 'MiguelReyes-PROG-01',
                    'date_of_birth' => '1984-02-27',
                ],
                'roles' => ['Profesor']
            ],

            // ESTUDIANTES
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos.rodriguez@sira.edu',
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
                'email' => 'ana.martinez@sira.edu',
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
            [
                'name' => 'Javier Méndez',
                'email' => 'javier.mendez@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Javier',
                    'last_name' => 'Méndez',
                    'card_id' => '002-1098765-4',
                    'academic_program_id' => 1,
                    'current_semester' => 2,
                    'enrollment_date' => now()->subMonths(8),
                    'enrollment_number' => '2024-0009',
                    'date_of_birth' => '2003-06-30',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Camila Flores',
                'email' => 'camila.flores@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Camila',
                    'last_name' => 'Flores',
                    'card_id' => '002-0987654-3',
                    'academic_program_id' => 1,
                    'current_semester' => 3,
                    'enrollment_date' => now()->subYears(1),
                    'enrollment_number' => '2024-0010',
                    'date_of_birth' => '2002-11-22',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Ricardo Castro',
                'email' => 'ricardo.castro@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Ricardo',
                    'last_name' => 'Castro',
                    'card_id' => '002-5555555-5',
                    'academic_program_id' => 1,
                    'current_semester' => 4,
                    'enrollment_date' => now()->subYears(2),
                    'enrollment_number' => '2024-0011',
                    'date_of_birth' => '2002-03-17',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Valentina Ortiz',
                'email' => 'valentina.ortiz@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Valentina',
                    'last_name' => 'Ortiz',
                    'card_id' => '002-6666666-6',
                    'academic_program_id' => 1,
                    'current_semester' => 2,
                    'enrollment_date' => now()->subMonths(7),
                    'enrollment_number' => '2024-0012',
                    'date_of_birth' => '2003-09-05',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Andrés Jiménez',
                'email' => 'andres.jimenez@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Andrés',
                    'last_name' => 'Jiménez',
                    'card_id' => '002-7777777-7',
                    'academic_program_id' => 1,
                    'current_semester' => 3,
                    'enrollment_date' => now()->subYears(1),
                    'enrollment_number' => '2024-0013',
                    'date_of_birth' => '2002-10-14',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Gabriela Ruiz',
                'email' => 'gabriela.ruiz@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Gabriela',
                    'last_name' => 'Ruiz',
                    'card_id' => '002-8888888-8',
                    'academic_program_id' => 1,
                    'current_semester' => 2,
                    'enrollment_date' => now()->subMonths(9),
                    'enrollment_number' => '2024-0014',
                    'date_of_birth' => '2003-03-21',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Fernando Medina',
                'email' => 'fernando.medina@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Fernando',
                    'last_name' => 'Medina',
                    'card_id' => '002-9999999-9',
                    'academic_program_id' => 1,
                    'current_semester' => 4,
                    'enrollment_date' => now()->subYears(2),
                    'enrollment_number' => '2024-0015',
                    'date_of_birth' => '2002-07-09',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Daniela Santos',
                'email' => 'daniela.santos@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Daniela',
                    'last_name' => 'Santos',
                    'card_id' => '002-1111111-1',
                    'academic_program_id' => 1,
                    'current_semester' => 3,
                    'enrollment_date' => now()->subYears(1),
                    'enrollment_number' => '2024-0016',
                    'date_of_birth' => '2002-12-05',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Mateo Herrera',
                'email' => 'mateo.herrera@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Mateo',
                    'last_name' => 'Herrera',
                    'card_id' => '002-2222222-2',
                    'academic_program_id' => 1,
                    'current_semester' => 2,
                    'enrollment_date' => now()->subMonths(8),
                    'enrollment_number' => '2024-0017',
                    'date_of_birth' => '2003-05-18',
                ],
                'roles' => ['Estudiante']
            ],
            [
                'name' => 'Carolina Vega',
                'email' => 'carolina.vega@sira.edu',
                'password' => Hash::make('1234'),
                'personData' => [
                    'first_name' => 'Carolina',
                    'last_name' => 'Vega',
                    'card_id' => '002-3333333-3',
                    'academic_program_id' => 1,
                    'current_semester' => 4,
                    'enrollment_date' => now()->subYears(2),
                    'enrollment_number' => '2024-0018',
                    'date_of_birth' => '2002-01-23',
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
