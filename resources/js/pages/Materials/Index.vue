<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseTable from '@/components/BaseTable.vue';
import ConfirmationDialog from '@/components/Modals/ConfirmationDialog.vue';
import type { BreadcrumbItem } from '@/types';
import { Columns, FileSpreadsheet } from 'lucide-vue-next';

interface Material {
    id: number;
    title: string;
    type: 'video' | 'pdf' | 'link' | 'document';
    file_path: string | null;
    url: string | null;
    description: string | null;
    created_at: string;
}

interface Props {
    materials: Material[];
    filters?: {
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Materiales', href: '/materials' }
];

const first = ref(0);
const showConfirmDialog = ref(false);
const materialToDelete = ref<Material | null>(null);

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        video: 'Video',
        pdf: 'PDF',
        link: 'Enlace',
        document: 'Documento'
    };
    return labels[type] || type;
};

const columns = [
    {
        key: 'title',
        field: 'title',
        header: 'Título',
        sortable: true,
        width: '30%',
    },
    {
        key: 'type',
        field: 'type',
        header: 'Tipo',
        sortable: true,
        width: '15%',
    },
    {
        key: 'description',
        field: 'description',
        header: 'Descripción',
        sortable: false,
        width: '35%',
        truncate: 50,
    },
    {
        key: 'created_at',
        field: 'created_at',
        header: 'Fecha',
        sortable: true,
        width: '20%',
        body: (row: Material) => new Date(row.created_at).toLocaleDateString()
    }
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

const handleView = (material: any) => {
    router.visit(route('materials.show', material.id));
};

const handleEdit = (material: any) => {
    router.visit(route('materials.edit', material.id));
};

const handleDelete = (material: any) => {
    materialToDelete.value = material;
    showConfirmDialog.value = true;
};

const confirmDelete = () => {
    if (materialToDelete.value) {
        router.delete(route('materials.destroy', materialToDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                showConfirmDialog.value = false;
                materialToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showConfirmDialog.value = false;
    materialToDelete.value = null;
};

const onPageChange = (event: any) => {
    first.value = event.first;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <div class="p-6 border-b bg-white rounded-[15px]">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            Materiales
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Gestiona los materiales de estudio
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <Link
                            :href="route('materials.create')"
                            class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-all duration-300 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1]"
                        >
                            <span>Nuevo Material</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- BaseTable -->
            <BaseTable
                :value="materials"
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
                emptyMessage="No hay materiales registrados todavía"
                @page-change="onPageChange"
                class="bg-white rounded-[15px] p-6"
                @view="handleView"
                @edit="handleEdit"
                @delete="handleDelete"
            >
                <template #body-type="{ data }">
                    <div
                        class="flex items-center gap-2 px-3 py-1.5 rounded-md w-fit"
                        :class="{
                            'text-red-500 bg-red-50': data.type === 'video',
                            'text-red-600 bg-red-50': data.type === 'pdf',
                            'text-blue-500 bg-blue-50': data.type === 'link',
                            'text-gray-600 bg-gray-50': data.type === 'document'
                        }"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-if="data.type === 'video'">
                            <path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.87a.5.5 0 0 0-.752-.432L16 10.5" />
                            <rect x="2" y="6" width="14" height="12" rx="2" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-else-if="data.type === 'pdf'">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            <path d="M10 9H8" />
                            <path d="M16 13H8" />
                            <path d="M16 17H8" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-else-if="data.type === 'link'">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-else>
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                        </svg>
                        <span class="capitalize font-medium">{{ getTypeLabel(data.type) }}</span>
                    </div>
                </template>
            </BaseTable>
        </div>

        <ConfirmationDialog
            :isOpen="showConfirmDialog"
            type="delete"
            title="Eliminar material"
            :entityName="materialToDelete?.title"
            confirmText="Eliminar"
            cancelText="Cancelar"
            @confirm="confirmDelete"
            @close="cancelDelete"
        />
    </AppLayout>
</template>
