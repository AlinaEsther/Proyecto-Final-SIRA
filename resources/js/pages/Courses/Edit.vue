<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { Save, X } from 'lucide-vue-next';
import BaseSelect from '@/components/BaseSelect.vue';

interface AcademicProgram {
    id: number;
    name: string;
    code: string;
}

interface Course {
    id: number;
    academic_program_id: number;
    name: string;
    code: string;
    description: string | null;
    credits: number;
    semester: number;
    status: 'active' | 'inactive';
}

const props = defineProps<{
    course: Course;
    academicPrograms?: AcademicProgram[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cursos', href: '/courses' },
    { title: 'Editar Curso', href: `/courses/${props.course.id}/edit` }
];

const form = useForm({
    academic_program_id: props.course.academic_program_id,
    name: props.course.name,
    code: props.course.code,
    description: props.course.description || '',
    credits: props.course.credits,
    semester: props.course.semester,
    status: props.course.status,
});

const statusOptions = [
    { id: 'active', name: 'Activo' },
    { id: 'inactive', name: 'Inactivo' },
];

const submit = () => {
    form.put(route('courses.update', props.course.id), {
        preserveScroll: true,
    });
};

const cancelForm = () => {
    router.visit(route('courses.index'));
};
</script>

<template>
    <Head title="Editar Curso" />

    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <!-- Encabezado -->
            <div class="p-6 border-b bg-white rounded-[15px] pb-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Curso</h1>
                        <p class="text-gray-500 mt-1">Modifica los datos del curso</p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="p-6 border-b bg-white rounded-[15px]">

                <!-- Sección 1: Programa Académico - 1 campo completo -->
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Programa Académico<span class="text-red-600">*</span>
                        </label>
                        <BaseSelect
                            v-model="form.academic_program_id"
                            :options="academicPrograms || []"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Seleccione un programa académico"
                            entity="programas académicos"
                            :class="{ 'border-red-500': form.errors.academic_program_id }"
                        >
                            <template #option="{ option }">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-blue-600 text-sm">{{ option.code }}</span>
                                    <span class="text-sm">{{ option.name }}</span>
                                </div>
                            </template>
                        </BaseSelect>
                        <span v-if="form.errors.academic_program_id" class="text-red-500 text-xs">{{ form.errors.academic_program_id }}</span>
                    </div>
                </div>

                <!-- Sección 2: Código y Nombre - 2 campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Código<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model="form.code"
                            type="text"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: MAT-101"
                            maxlength="20"
                            :class="{ 'border-red-500': form.errors.code }"
                            required
                        />
                        <span v-if="form.errors.code" class="text-red-500 text-xs">{{ form.errors.code }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre del Curso<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: Matemáticas I"
                            maxlength="255"
                            :class="{ 'border-red-500': form.errors.name }"
                            required
                        />
                        <span v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</span>
                    </div>
                </div>

                <!-- Sección 3: Créditos, Semestre y Estado - 3 campos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Créditos<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model.number="form.credits"
                            type="number"
                            min="1"
                            max="10"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.credits }"
                            required
                        />
                        <span v-if="form.errors.credits" class="text-red-500 text-xs">{{ form.errors.credits }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Semestre<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model.number="form.semester"
                            type="number"
                            min="1"
                            max="12"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.semester }"
                            required
                        />
                        <span v-if="form.errors.semester" class="text-red-500 text-xs">{{ form.errors.semester }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Estado<span class="text-red-600">*</span>
                        </label>
                        <BaseSelect
                            v-model="form.status"
                            :options="statusOptions"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Seleccione un estado"
                            entity="estados"
                            :filter="false"
                            :class="{ 'border-red-500': form.errors.status }"
                        />
                        <span v-if="form.errors.status" class="text-red-500 text-xs">{{ form.errors.status }}</span>
                    </div>
                </div>

                <!-- Sección 4: Descripción - 1 campo completo -->
                <div class="grid grid-cols-1 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Descripción del curso (opcional)"
                            :class="{ 'border-red-500': form.errors.description }"
                        ></textarea>
                        <span v-if="form.errors.description" class="text-red-500 text-xs">{{ form.errors.description }}</span>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end gap-4">
                    <button
                        type="button"
                        @click="cancelForm"
                        class="flex items-center gap-2 rounded-full px-4 py-2 font-semibold text-white transition-transform duration-200 hover:scale-[1.05] focus:scale-[1] bg-gray-500/75 hover:bg-gray-600"
                    >
                        <X class="h-5 w-5"/>
                        <span>Cancelar</span>
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-transform duration-200 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <Save class="h-5 w-5"/>
                        <span>{{ form.processing ? 'Actualizando...' : 'Actualizar' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
