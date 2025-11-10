<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir entidades del sistema
        $entities = [
            'users',
            'academic-programs',
            'courses',
            'sections',
            'materials',
            'activities',
            'grades',
            'recommendations',
        ];

        // Definir acciones CRUD estándar
        $actions = ['view', 'create', 'update', 'delete'];

        // Crear permisos CRUD para cada entidad
        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "$entity.$action",
                    'guard_name' => 'web'
                ]);
            }
        }

        // Permisos especiales (que no siguen el patrón CRUD)
        $specialPermissions = [
            'section-students.manage',
            'materials.download',
            'activities.submit',
        ];

        foreach ($specialPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }
    }
}

