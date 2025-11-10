<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

interface Person {
    full_name: string;
}

interface Professor {
    id: number;
    name?: string;
    person?: Person;
}

interface AcademicProgram {
    name: string;
}

interface Course {
    id: number;
    code: string;
    name: string;
    academic_program?: AcademicProgram;
}

interface Section {
    id: number;
    course_id: number;
    professor_id: number;
    name: string;
    academic_period: string;
    schedule?: any;
    max_students: number;
    status: string;
}

const props = defineProps<{
    section: Section;
    courses?: Course[];
    professors?: Professor[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Secciones', href: '/sections' },
    { title: 'Editar Sección', href: `/sections/${props.section.id}/edit` }
];

const form = useForm({
    course_id: props.section.course_id,
    professor_id: props.section.professor_id,
    name: props.section.name,
    academic_period: props.section.academic_period,
    schedule: props.section.schedule,
    max_students: props.section.max_students,
    status: props.section.status,
});

const submit = () => {
    form.put(route('sections.update', props.section.id));
};
</script>

<template>
    <Head title="Editar Sección" />

    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <!-- Header -->
            <div class="p-6 border-b bg-white rounded-[15px]">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Sección</h1>
                        <p class="text-gray-500 mt-1">Actualiza la información de la sección</p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <div class="p-6 bg-white rounded-[15px]">
                <div class="max-w-4xl">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Curso -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Curso *</label>
                                <select
                                    v-model="form.course_id"
                                    class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Seleccione un curso</option>
                                    <option v-for="course in courses" :key="course.id" :value="course.id">
                                        {{ course.code }} - {{ course.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.course_id" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.course_id }}
                                </div>
                            </div>

                            <!-- Profesor -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Profesor *</label>
                                <select
                                    v-model="form.professor_id"
                                    class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Seleccione un profesor</option>
                                    <option v-for="professor in professors" :key="professor.id" :value="professor.id">
                                        {{ professor.person?.full_name || professor.name || `Profesor ${professor.id}` }}
                                    </option>
                                </select>
                                <div v-if="form.errors.professor_id" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.professor_id }}
                                </div>
                            </div>

                            <!-- Nombre de sección -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Sección *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ej: Sección A"
                                    required
                                />
                                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Periodo académico -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Periodo Académico *</label>
                                <input
                                    v-model="form.academic_period"
                                    type="text"
                                    class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ej: 2025-1"
                                    required
                                />
                                <div v-if="form.errors.academic_period" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.academic_period }}
                                </div>
                            </div>

                            <!-- Capacidad máxima -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad Máxima *</label>
                                <input
                                    v-model="form.max_students"
                                    type="number"
                                    min="1"
                                    class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                <div v-if="form.errors.max_students" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.max_students }}
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                                <select
                                    v-model="form.status"
                                    class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="open">Abierto</option>
                                    <option value="closed">Cerrado</option>
                                    <option value="completed">Completado</option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.status }}
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="mt-6 flex justify-end gap-3">
                            <Link
                                :href="route('sections.show', section.id)"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded shadow transition"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition"
                                :disabled="form.processing"
                            >
                                Actualizar Sección
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

