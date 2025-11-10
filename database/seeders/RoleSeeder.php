<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear cache de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Lista de roles con sus permisos
        $rolesList = [
            [
                'name' => 'Admin',
                'permissions' => 'all' // Acceso total al sistema
            ],
            [
                'name' => 'Profesor',
                'permissions' => [
                    // Cursos
                    'courses.view',

                    // Secciones
                    'sections.view',
                    'sections.update',
                    'section-students.manage',

                    // Materiales
                    'materials.view',
                    'materials.create',
                    'materials.update',
                    'materials.delete',

                    // Actividades
                    'activities.view',
                    'activities.create',
                    'activities.update',
                    'activities.delete',

                    // Calificaciones
                    'grades.view',
                    'grades.create',
                    'grades.update',

                    // Recomendaciones
                    'recommendations.view',
                    'recommendations.create',
                    'recommendations.update',
                    'recommendations.delete',
                ]
            ],
            [
                'name' => 'Estudiante',
                'permissions' => [
                    // Solo visualización
                    'courses.view',
                    'sections.view',
                    'materials.view',
                    'materials.download',
                    'activities.view',
                    'activities.submit',
                    'grades.view',
                    'recommendations.view',
                ]
            ],
        ];

        // Crear roles y asignar permisos
        foreach ($rolesList as $roleData) {
            $role = Role::firstOrCreate(['name' => $roleData['name']]);

            if ($roleData['permissions'] === 'all') {
                // Admin tiene todos los permisos
                $role->givePermissionTo(Permission::all());
            } else {
                // Asignar permisos específicos
                $role->syncPermissions($roleData['permissions']);
            }
        }
    }
}


