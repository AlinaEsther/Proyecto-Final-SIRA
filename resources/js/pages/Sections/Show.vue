<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

interface Person {
    full_name?: string;
    first_name?: string;
    last_name?: string;
}

interface Professor {
    name?: string;
    person?: Person;
}

interface Course {
    name?: string;
    code?: string;
}

interface Student {
    id: number;
    person: Person;
    pivot: {
        grade_p1?: number | string | null;
        grade_p2?: number | string | null;
        grade_p3?: number | string | null;
        grade_exam?: number | string | null;
        current_grade?: number | string | null;
        final_grade?: number | string | null;
        letter_grade?: string | null;
    };
}

interface Material {
    id: number;
    title: string;
    type: string;
    pivot: {
        is_required: boolean;
    };
}

interface Section {
    id: number;
    name: string;
    academic_period: string;
    status: string;
    max_students: number;
    course: Course;
    professor: Professor;
    students?: Student[];
    materials?: Material[];
}

interface Statistics {
    enrolled_count: number;
    average_grade?: number;
    activities_count: number;
}

const props = defineProps<{
    section: Section;
    statistics: Statistics;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Secciones', href: '/sections' },
    { title: props.section.name, href: `/sections/${props.section.id}` }
];

// Helper para formatear notas de forma segura
const formatGrade = (grade?: number | string | null): string => {
    if (grade === null || grade === undefined) return '-';
    const numGrade = typeof grade === 'string' ? parseFloat(grade) : grade;
    return isNaN(numGrade) ? '-' : numGrade.toFixed(2);
};
</script>

<template>
    <Head :title="`Sección ${section.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <!-- Header -->
            <div class="p-6 border-b bg-white rounded-[15px]">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ section.course.name }}</h1>
                        <p class="text-gray-500 mt-1">{{ section.name }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <Link
                            :href="route('sections.edit', section.id)"
                            class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-all duration-300 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1]"
                        >
                            <span>Editar</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Información general -->
            <div class="p-6 bg-white rounded-[15px]">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Información General</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Curso</p>
                        <p class="font-medium text-gray-800">{{ section.course?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Profesor</p>
                        <p class="font-medium text-gray-800">
                            {{ section.professor?.person?.full_name || section.professor?.name || 'Sin asignar' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Periodo Académico</p>
                        <p class="font-medium text-gray-800">{{ section.academic_period || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Estado</p>
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="{
                                'bg-green-100 text-green-800': section.status === 'open',
                                'bg-red-100 text-red-800': section.status === 'closed',
                                'bg-gray-100 text-gray-800': section.status === 'completed',
                            }"
                        >
                            {{ section.status || 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-white rounded-[15px]">
                    <p class="text-sm text-gray-500">Estudiantes Inscritos</p>
                    <p class="text-4xl font-bold text-blue-800 mt-2">{{ statistics.enrolled_count }} / {{ section.max_students }}</p>
                </div>
                <div class="p-6 bg-white rounded-[15px]">
                    <p class="text-sm text-gray-500">Promedio General</p>
                    <p class="text-4xl font-bold text-blue-800 mt-2">{{ statistics.average_grade?.toFixed(2) || 'N/A' }}</p>
                </div>
                <div class="p-6 bg-white rounded-[15px]">
                    <p class="text-sm text-gray-500">Actividades</p>
                    <p class="text-4xl font-bold text-blue-800 mt-2">{{ statistics.activities_count }}</p>
                </div>
            </div>

            <!-- Estudiantes -->
            <div class="p-6 bg-white rounded-[15px]">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Estudiantes Inscritos</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Nombre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                P1
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                P2
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                P3
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Examen
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Nota Actual
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Nota Final
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Literal
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="!section.students || section.students.length === 0">
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No hay estudiantes inscritos todavía
                            </td>
                        </tr>
                        <tr v-for="student in section.students" :key="student.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                {{ student.person?.full_name || `${student.person?.first_name || ''} ${student.person?.last_name || ''}`.trim() || 'Sin nombre' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ formatGrade(student.pivot.grade_p1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ formatGrade(student.pivot.grade_p2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ formatGrade(student.pivot.grade_p3) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ formatGrade(student.pivot.grade_exam) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                {{ formatGrade(student.pivot.current_grade) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-800">
                                {{ formatGrade(student.pivot.final_grade) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="student.pivot.letter_grade"
                                    class="px-3 py-1 text-sm font-bold rounded-full"
                                    :class="{
                                        'bg-green-100 text-green-800': student.pivot.letter_grade === 'A',
                                        'bg-blue-100 text-blue-800': student.pivot.letter_grade === 'B',
                                        'bg-yellow-100 text-yellow-800': student.pivot.letter_grade === 'C',
                                        'bg-red-100 text-red-800': student.pivot.letter_grade === 'F',
                                    }"
                                >
                                    {{ student.pivot.letter_grade }}
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Materiales -->
            <div class="p-6 bg-white rounded-[15px]">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Materiales</h3>
                <div v-if="!section.materials || section.materials.length === 0" class="text-center text-sm text-gray-500 py-4">
                    No hay materiales disponibles todavía
                </div>
                <div v-else class="space-y-2">
                    <div
                        v-for="material in section.materials"
                        :key="material.id"
                        class="flex justify-between items-center p-4 border border-gray-200 rounded hover:bg-gray-50 transition"
                    >
                        <div>
                            <p class="font-medium text-gray-800">{{ material.title }}</p>
                            <p class="text-sm text-gray-500">{{ material.type }}</p>
                        </div>
                        <span
                            v-if="material.pivot.is_required"
                            class="bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full"
                        >
                            Requerido
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
