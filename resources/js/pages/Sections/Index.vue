<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import BaseTable from '@/components/BaseTable.vue';
import ConfirmationDialog from '@/components/Modals/ConfirmationDialog.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { BreadcrumbItem } from '@/types';
import { FileSpreadsheet, Columns } from 'lucide-vue-next';

interface FilterType {
    course_id?: string;
    academic_period?: string;
    status?: string;
    search?: string;
}

interface SectionData {
    id: number;
    name: string;
    academic_period: string;
    status: string;
    course: {
        name: string;
    };
    professor?: {
        name?: string;
        person?: {
            full_name: string;
        };
    };
}

interface PaginatedSections {
    data: SectionData[];
    from: number;
    to: number;
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    links: {
        label: string;
        url: string | null;
        active: boolean;
    }[];
}

const props = defineProps<{
    sections?: PaginatedSections;
    filters?: FilterType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Secciones', href: '/sections' }
];

// Configuración de columnas para BaseTable
const columns = [
    {
        key: 'course',
        field: 'course',
        header: 'Curso',
        sortable: true,
        width: '25%',
        body: (row: SectionData) => row.course?.name || 'N/A'
    },
    {
        key: 'name',
        field: 'name',
        header: 'Sección',
        sortable: true,
        width: '15%',
    },
    {
        key: 'professor',
        field: 'professor',
        header: 'Profesor',
        sortable: false,
        width: '25%',
        body: (row: SectionData) => row.professor?.person?.full_name || row.professor?.name || 'Sin asignar'
    },
    {
        key: 'academic_period',
        field: 'academic_period',
        header: 'Periodo',
        sortable: true,
        width: '15%',
    },
    {
        key: 'status',
        field: 'status',
        header: 'Estado',
        sortable: true,
        width: '15%',
    },
];

// Botones del header
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

// Estado de paginación
const first = ref(0);

// Estado del diálogo de confirmación
const showConfirmDialog = ref(false);
const sectionToDelete = ref<SectionData | null>(null);

const onPageChange = (event: any) => {
    first.value = event.first;
};

// Manejadores de eventos
const handleView = (section: SectionData) => {
    router.visit(route('sections.show', section.id));
};

const handleEdit = (section: SectionData) => {
    router.visit(route('sections.edit', section.id));
};

const handleDelete = (section: SectionData) => {
    sectionToDelete.value = section;
    showConfirmDialog.value = true;
};

const confirmDelete = () => {
    if (sectionToDelete.value) {
        router.delete(route('sections.destroy', sectionToDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                showConfirmDialog.value = false;
                sectionToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showConfirmDialog.value = false;
    sectionToDelete.value = null;
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
                            Secciones
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Gestiona las secciones académicas del sistema
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <Link
                            :href="route('sections.create')"
                            class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-all duration-300 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1]"
                        >
                            <span>Nueva Sección</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- BaseTable -->
            <BaseTable
                :value="sections?.data"
                :columns="columns"
                :headerButtons="headerButtons"
                :loading="false"
                :actions-type="'default'"
                :paginator="true"
                :rows-per-page-options="[5, 10, 20]"
                :totalVisible="4"
                :first="first"
                data-key="id"
                emptyMessage="No hay secciones registradas todavía"
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
                            'bg-green-100 text-green-800': data.status === 'open',
                            'bg-red-100 text-red-800': data.status === 'closed',
                            'bg-gray-100 text-gray-800': data.status === 'completed'
                        }"
                    >
                        {{ data.status }}
                    </span>
                </template>
            </BaseTable>
        </div>

        <!-- ConfirmationDialog -->
        <ConfirmationDialog
            :isOpen="showConfirmDialog"
            type="delete"
            title="Eliminar sección"
            :entityName="sectionToDelete?.name"
            confirmText="Eliminar"
            cancelText="Cancelar"
            @confirm="confirmDelete"
            @close="cancelDelete"
        />
    </AppLayout>
</template>
