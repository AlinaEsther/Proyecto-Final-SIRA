<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { Save, X, Paperclip, FileText, Video, Link as LinkIcon, File } from 'lucide-vue-next';
import BaseSelect from '@/components/BaseSelect.vue';
import MaterialsAttachModal from '@/components/Modals/MaterialsAttachModal.vue';
import { computed, ref } from 'vue';

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

interface Material {
    id: number;
    title: string;
    type: 'video' | 'pdf' | 'link' | 'document';
    pivot?: {
        is_required: boolean;
    };
}

const { courses, professors, materials } = defineProps<{
    courses?: Course[];
    professors?: Professor[];
    materials?: Material[];
}>();

// Map professors to include full_name at root level for BaseSelect
const mappedProfessors = computed(() =>
    professors?.map(prof => ({
        id: prof.id,
        full_name: prof.person?.full_name || 'Sin nombre'
    })) || []
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Secciones', href: '/sections' },
    { title: 'Nueva Sección', href: '/sections/create' }
];

const form = useForm({
    course_id: '',
    professor_id: '',
    name: '',
    academic_period: '',
    schedule: null,
    max_students: 30,
    status: 'open',
    materials: [] as Array<{ id: number; is_required: boolean }>,
});

// Modal state
const showMaterialsModal = ref(false);

// Computed para obtener materiales seleccionados con sus detalles
const selectedMaterialsDetails = computed(() => {
    return form.materials
        .map(mat => {
            const material = materials?.find(m => m.id === mat.id);
            return material ? { ...material, is_required: mat.is_required } : null;
        })
        .filter(m => m !== null);
});

const typeIcons = {
    video: Video,
    pdf: FileText,
    link: LinkIcon,
    document: File
};

const typeColors = {
    video: 'bg-red-100 text-red-800',
    pdf: 'bg-red-100 text-red-800',
    link: 'bg-blue-100 text-blue-800',
    document: 'bg-gray-100 text-gray-800'
};

const handleMaterialsSave = (materials: Array<{ id: number; is_required: boolean }>) => {
    form.materials = materials;
};

const statusOptions = [
    { id: 'open', name: 'Abierto' },
    { id: 'closed', name: 'Cerrado' },
    { id: 'completed', name: 'Completado' },
];

const submit = () => {
    form.post(route('sections.store'), {
        preserveScroll: true,
    });
};

const cancelForm = () => {
    router.visit(route('sections.index'));
};
</script>

<template>
    <Head title="Nueva Sección" />

    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <!-- Encabezado -->
            <div class="p-6 border-b bg-white rounded-[15px] pb-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Nueva Sección</h1>
                        <p class="text-gray-500 mt-1">Crea una nueva sección para un curso</p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="p-6 border-b bg-white rounded-[15px]">

                <!-- Sección 1: Curso y Profesor - 2 campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Curso -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Curso<span class="text-red-600">*</span>
                        </label>
                        <BaseSelect
                            v-model="form.course_id"
                            :options="courses || []"
                            optionLabel="code"
                            optionValue="id"
                            placeholder="Seleccione un curso"
                            entity="cursos"
                            :class="{ 'border-red-500': form.errors.course_id }"
                        >
                            <template #option="{ option }">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-blue-600 text-sm">{{ option.code }}</span>
                                    <span class="text-sm">{{ option.name }}</span>
                                </div>
                            </template>
                        </BaseSelect>
                        <span v-if="form.errors.course_id" class="text-red-500 text-xs">{{ form.errors.course_id }}</span>
                    </div>

                    <!-- Profesor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Profesor<span class="text-red-600">*</span>
                        </label>
                        <BaseSelect
                            v-model="form.professor_id"
                            :options="mappedProfessors"
                            optionLabel="full_name"
                            optionValue="id"
                            placeholder="Seleccione un profesor"
                            entity="profesores"
                            :class="{ 'border-red-500': form.errors.professor_id }"
                        />
                        <span v-if="form.errors.professor_id" class="text-red-500 text-xs">{{ form.errors.professor_id }}</span>
                    </div>
                </div>

                <!-- Sección 2: Nombre, Periodo y Capacidad - 3 campos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Nombre de sección -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre de Sección<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: Sección A"
                            :class="{ 'border-red-500': form.errors.name }"
                            required
                        />
                        <span v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</span>
                    </div>

                    <!-- Periodo académico -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Periodo Académico<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model="form.academic_period"
                            type="text"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: 2025-1"
                            :class="{ 'border-red-500': form.errors.academic_period }"
                            required
                        />
                        <span v-if="form.errors.academic_period" class="text-red-500 text-xs">{{ form.errors.academic_period }}</span>
                    </div>

                    <!-- Capacidad máxima -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Capacidad Máxima<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model="form.max_students"
                            type="number"
                            min="1"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.max_students }"
                            required
                        />
                        <span v-if="form.errors.max_students" class="text-red-500 text-xs">{{ form.errors.max_students }}</span>
                    </div>
                </div>

                <!-- Sección 3: Estado - 1 campo -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Estado -->
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

                <!-- Sección 4: Materiales - 1 campo completo -->
                <div v-if="materials" class="grid grid-cols-1 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Materiales
                        </label>

                        <!-- Botón para abrir modal -->
                        <button
                            type="button"
                            @click="showMaterialsModal = true"
                            class="flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-colors text-sm font-medium text-gray-700 w-full md:w-auto"
                        >
                            <Paperclip class="h-5 w-5" />
                            <span>Gestionar Materiales ({{ form.materials.length }})</span>
                        </button>

                        <!-- Lista de materiales seleccionados -->
                        <div v-if="selectedMaterialsDetails.length > 0" class="mt-4 space-y-2">
                            <div
                                v-for="material in selectedMaterialsDetails"
                                :key="material.id"
                                class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg"
                            >
                                <component :is="typeIcons[material.type]" class="h-5 w-5 text-gray-600" />
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 text-sm">{{ material.title }}</p>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="material.is_required ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'"
                                >
                                    {{ material.is_required ? 'Requerido' : 'Opcional' }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-xs text-gray-500 mt-2">No se han seleccionado materiales</p>

                        <span v-if="form.errors.materials" class="text-red-500 text-xs">{{ form.errors.materials }}</span>
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
                        <span>{{ form.processing ? 'Guardando...' : 'Guardar' }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Modal de materiales -->
        <MaterialsAttachModal
            :isOpen="showMaterialsModal"
            :materials="materials || []"
            :attachedMaterials="form.materials"
            @close="showMaterialsModal = false"
            @save="handleMaterialsSave"
        />
    </AppLayout>
</template>
