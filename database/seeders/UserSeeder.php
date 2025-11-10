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
        // Lista de usuarios con sus datos de persona y roles
        $usersList = [
            // ADMINISTRADOR DEL SISTEMA
            [
                'name' => 'Admin SIRA',
                'email' => 'sisadmin@sira.test',
                'password' => Hash::make('admin'),
                'personData' => [
                    'first_name' => 'Admin',
                    'last_name' => 'Sistema',
                    'card_id' => '000-0000000-0',
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
                    'department' => 'Ingeniería',
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
                    'department' => 'Ingeniería',
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
                ],
                'roles' => ['Estudiante']
            ],
        ];

        // Procesar cada usuario
        foreach ($usersList as $userData) {
            // Crear usuario
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);

            // Crear datos de persona si existen
            if (isset($userData['personData'])) {
                $personData = $userData['personData'];
                $personData['user_id'] = $user->id;

                Person::create($personData);
            }

            // Asignar roles si existen
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
