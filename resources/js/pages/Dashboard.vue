<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {type BreadcrumbItem} from '@/types';
import {InfoIcon} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {title: 'Dashboard', href: '/dashboard'}
];

const handleAddActivity = () => {
    console.log('Añadir actividad');
    // Implementar lógica para añadir actividad
};

const handleAddEvent = () => {
    console.log('Añadir evento');
    // Implementar lógica para añadir evento
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full rounded-xl">

            <!-- Sección izquierda: tarjetas y gráficas -->

            <div class="flex-1 h-full overflow-y-hidden">
                <div class="p-6 flex flex-col gap-8">

                    <!-- Grid 2 columnas para A y B -->
                    <div class="flex flex-row justify-between">

                        <!-- Columna 1: Tarjeta A + Gráfica A -->
                        <div class="flex flex-col w-1/2 h-95 gap-0.5 mr-6">
                            <!-- Tarjeta A -->
                            <div class="flex-none relative shadow-md bg-white p-6 flex items-start rounded h-1/3">
                                <div class="flex-1">
                                    <h2 class="text-xl font-semibold text-gray-700">Secciones Activas</h2>
                                    <div class="text-5xl font-bold text-blue-800 mt-2">35</div>
                                </div>
                                <InfoIcon class="w-7 h-7 text-gray-400 top-4 right-4"/>
                            </div>
                            <!-- Gráfica A -->
                            <div
                                class="flex-1 relative bg-white shadow-md p-4 flex items-center justify-center rounded">
                                [Listado]
                            </div>
                        </div>

                        <!-- Columna 2: Tarjeta B + Gráfica B -->
                        <div class="flex flex-col w-1/2 h-95 gap-0.5 mr-0">
                            <!-- Tarjeta B -->
                            <div class="flex-none relative shadow-md bg-white p-6 flex items-start rounded h-1/3">
                                <div class="flex-1">
                                    <h2 class="text-xl font-semibold text-gray-700">Progreso de calificaiones</h2>
                                    <div class="text-5xl font-bold text-blue-800 mt-2">431</div>
                                </div>
                                <InfoIcon class="w-7 h-7 text-gray-400 top-4 right-4"/>
                            </div>
                            <!-- Gráfica B -->
                            <div
                                class="flex-1 relative bg-white shadow-md p-4 flex items-center justify-center rounded">
                                [Grafico]
                            </div>
                        </div>

                    </div>

                    <!-- Sección grande completa -->
                    <div class="flex justify-center">
                        <div class="flex flex-col w-full h-96 gap-0.5">
                            <!-- Tarjeta Grande -->
                            <div class="flex-none relative shadow-md bg-white p-6 flex flex-col rounded">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-xl font-semibold text-gray-700">Materiales sugeridos</h2>
                                    <InfoIcon class="w-7 h-7 text-gray-400 absolute top-4 right-4"/>
                                </div>
                                <div class="mt-1">
                                    <div class="text-5xl font-bold text-blue-800">25 libros</div>
                                    <div class="text-sm text-gray-500 my-1">A la fecha</div>
                                </div>
                            </div>
                            <!-- Gráfica Grande -->
                            <div
                                class="flex-1 relative bg-white shadow-md p-4 flex items-center justify-center rounded">
                                [listado]
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>
