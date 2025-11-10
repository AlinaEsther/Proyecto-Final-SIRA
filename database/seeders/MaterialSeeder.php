<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            [
                'title' => 'Introducción a Python',
                'type' => 'video',
                'url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'Video tutorial de Python para principiantes',
            ],
            [
                'title' => 'Guía de JavaScript',
                'type' => 'pdf',
                'url' => 'https://example.com/javascript-guide.pdf',
                'description' => 'Documento completo sobre JavaScript',
            ],
            [
                'title' => 'Documentación oficial de Laravel',
                'type' => 'link',
                'url' => 'https://laravel.com/docs',
                'description' => 'Documentación oficial del framework Laravel',
            ],
            [
                'title' => 'Algoritmos y Estructuras de Datos',
                'type' => 'document',
                'url' => 'https://example.com/algorithms.pdf',
                'description' => 'Libro sobre algoritmos fundamentales',
            ],
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }
    }
}
