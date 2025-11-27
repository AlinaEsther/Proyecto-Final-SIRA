<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseTable from '@/components/BaseTable.vue';
import ConfirmationDialog from '@/components/Modals/ConfirmationDialog.vue';
import type { BreadcrumbItem } from '@/types';
import { FileSpreadsheet, Columns } from 'lucide-vue-next';

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
    created_at: string;
    updated_at: string;
    academic_program: AcademicProgram;
}

interface PaginatedData {
    data: Course[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    courses: PaginatedData;
    filters?: {
        search?: string;
        academic_program_id?: number;
        semester?: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cursos', href: '/courses' }
];

const first = ref(0);

// Estado del diálogo de confirmación
const showConfirmDialog = ref(false);
const courseToDelete = ref<Course | null>(null);

const columns = [
    {
        key: 'code',
        field: 'code',
        header: 'Código',
        sortable: true,
        width: '10%',
    },
    {
        key: 'name',
        field: 'name',
        header: 'Nombre del Curso',
        sortable: true,
        width: '25%',
    },
    {
        key: 'academic_program',
        field: 'academic_program',
        header: 'Programa Académico',
        sortable: true,
        width: '20%',
        body: (row: Course) => row.academic_program?.name || 'N/A'
    },
    {
        key: 'credits',
        field: 'credits',
        header: 'Créditos',
        sortable: true,
        width: '10%',
        align: 'center' as const,
    },
    {
        key: 'semester',
        field: 'semester',
        header: 'Semestre',
        sortable: true,
        width: '10%',
        align: 'center' as const,
    },
    {
        key: 'status',
        field: 'status',
        header: 'Estado',
        sortable: true,
        width: '10%',
    },
    {
        key: 'description',
        field: 'description',
        header: 'Descripción',
        sortable: false,
        width: '15%',
        truncate: 50,
    },
];

const headerButtons = [
    {
        id: 'columns',
        label: 'Columnas',
        icon: Columns
    },
    {
        id: 'excel',
        label: 'Excel',
        icon: FileSpreadsheet
    }
];

const handleView = (course: Course) => {
    router.visit(route('courses.show', course.id));
};

const handleEdit = (course: Course) => {
    router.visit(route('courses.edit', course.id));
};

const handleDelete = (course: Course) => {
    courseToDelete.value = course;
    showConfirmDialog.value = true;
};

const confirmDelete = () => {
    if (courseToDelete.value) {
        router.delete(route('courses.destroy', courseToDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                showConfirmDialog.value = false;
                courseToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showConfirmDialog.value = false;
    courseToDelete.value = null;
};

const onPageChange = (event: any) => {
    first.value = event.first;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <!-- Header con título y botón -->
            <div class="p-6 border-b bg-white rounded-[15px]">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            Cursos
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Gestiona los cursos académicos del sistema
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <Link
                            :href="route('courses.create')"
                            class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-all duration-300 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1]"
                        >
                            <span>Nuevo Curso</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- BaseTable -->
            <BaseTable
                :value="courses?.data"
                :columns="columns"
                :headerButtons="headerButtons"
                :loading="false"
                :actions-type="'default'"
                :paginator="true"
                :rows="10"
                :rows-per-page-options="[5, 10, 20]"
                :totalVisible="4"
                :first="first"
                data-key="id"
                emptyMessage="No hay cursos registrados todavía"
                @page-change="onPageChange"
                class="bg-white rounded-[15px] p-6"
                @view="handleView"
                @edit="handleEdit"
                @delete="handleDelete"
            >
                <!-- Slot para status con badge de color -->
                <template #body-status="{ data }">
                    <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="{
                            'bg-green-100 text-green-800': data.status === 'active',
                            'bg-red-100 text-red-800': data.status !== 'active'
                        }"
                    >
                        {{ data.status === 'active' ? 'Activo' : 'Inactivo' }}
                    </span>
                </template>
            </BaseTable>
        </div>

        <!-- ConfirmationDialog -->
        <ConfirmationDialog
            :isOpen="showConfirmDialog"
            type="delete"
            title="Eliminar curso"
            :entityName="courseToDelete?.name"
            confirmText="Eliminar"
            cancelText="Cancelar"
            @confirm="confirmDelete"
            @close="cancelDelete"
        />
    </AppLayout>
</template>
