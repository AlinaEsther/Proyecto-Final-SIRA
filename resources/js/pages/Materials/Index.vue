<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseTable from '@/components/BaseTable.vue';
import ConfirmationDialog from '@/components/Modals/ConfirmationDialog.vue';
import MaterialReviewModal from '@/components/Modals/MaterialReviewModal.vue';
import type { BreadcrumbItem } from '@/types';
import { Columns, FileSpreadsheet } from 'lucide-vue-next';

interface Material {
    id: number;
    title: string;
    author: string | null;
    type: 'video' | 'pdf' | 'link' | 'document';
    file_path: string | null;
    url: string | null;
    description: string | null;
    created_at: string;
}

interface MaterialRequest {
    id: number;
    title: string;
    author: string | null;
    type_requested: 'video' | 'pdf' | 'link' | 'document';
    description: string | null;
    url: string | null;
    status: 'pending' | 'approved' | 'rejected';
    requester: {
        id: number;
        person: {
            first_name: string;
            last_name: string;
        };
    };
    created_at: string;
}

interface Props {
    materials: Material[];
    materialRequests?: MaterialRequest[] | null;
    filters?: {
        search?: string;
    };
}

const props = defineProps<Props>();
const page = usePage();
const isAdmin = computed(() => (page.props.auth as any)?.can?.isAdmin);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Materiales', href: '/materials' }
];

// Inicializar activeTab desde localStorage o desde URL query param
const getInitialTab = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam === 'solicitudes') return 1;

    const saved = localStorage.getItem('materials_active_tab');
    return saved ? parseInt(saved) : 0;
};

const activeTab = ref(getInitialTab());

// Persistir tab activo en localStorage
watch(activeTab, (newTab) => {
    localStorage.setItem('materials_active_tab', newTab.toString());
});

// Leer tab desde query param al montar
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam === 'solicitudes') {
        activeTab.value = 1;
    }
});
const first = ref(0);
const showConfirmDialog = ref(false);
const materialToDelete = ref<Material | null>(null);
const showReviewModal = ref(false);
const requestToReview = ref<MaterialRequest | null>(null);

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
        width: '25%',
    },
    {
        key: 'author',
        field: 'author',
        header: 'Autor',
        sortable: true,
        width: '15%',
    },
    {
        key: 'type',
        field: 'type',
        header: 'Tipo',
        sortable: true,
        width: '10%',
    },
    {
        key: 'description',
        field: 'description',
        header: 'Descripción',
        sortable: false,
        width: '30%',
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

const requestColumns = [
    {
        key: 'title',
        field: 'title',
        header: 'Título',
        sortable: true,
        width: '20%',
    },
    {
        key: 'author',
        field: 'author',
        header: 'Autor',
        sortable: true,
        width: '12%',
    },
    {
        key: 'type_requested',
        field: 'type_requested',
        header: 'Tipo',
        sortable: true,
        width: '8%',
    },
    {
        key: 'requester',
        header: 'Solicitante',
        sortable: false,
        width: '12%',
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
        truncate: 40,
    },
    {
        key: 'created_at',
        field: 'created_at',
        header: 'Fecha',
        sortable: true,
        width: '10%',
    },
    {
        key: 'actions',
        header: 'Acciones',
        sortable: false,
        width: '13%',
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

const handleApproveRequest = (request: MaterialRequest) => {
    requestToReview.value = request;
    showReviewModal.value = true;
};

const handleRejectRequest = (request: MaterialRequest) => {
    requestToReview.value = request;
    showReviewModal.value = true;
};

const confirmApprove = (data: { title: string; author: string; url: string; description: string }) => {
    if (!requestToReview.value) return;

    router.post(route('material-requests.approve', requestToReview.value.id), data, {
        preserveScroll: true,
        onFinish: () => {
            showReviewModal.value = false;
            requestToReview.value = null;
        }
    });
};

const confirmReject = (notes: string) => {
    if (!requestToReview.value) return;

    router.post(route('material-requests.reject', requestToReview.value.id), {
        admin_notes: notes
    }, {
        preserveScroll: true,
        onFinish: () => {
            showReviewModal.value = false;
            requestToReview.value = null;
        }
    });
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        approved: 'Aprobado',
        rejected: 'Rechazado'
    };
    return labels[status] || status;
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
                            {{ activeTab === 0 ? 'Gestiona los materiales de estudio' : 'Revisa las solicitudes de materiales' }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <Link
                            v-if="activeTab === 0"
                            :href="route('materials.create')"
                            class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-all duration-300 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1]"
                        >
                            <span>Nuevo Material</span>
                        </Link>
                    </div>
                </div>

                <!-- Tabs (solo visible para admin) -->
                <div v-if="isAdmin" class="flex gap-2 mt-6 border-b border-gray-200">
                    <button
                        @click="activeTab = 0"
                        class="px-6 py-3 font-semibold transition-all duration-200 border-b-2"
                        :class="{
                            'text-blue-600 border-blue-600': activeTab === 0,
                            'text-gray-500 border-transparent hover:text-gray-700': activeTab !== 0
                        }"
                    >
                        Materiales
                    </button>
                    <button
                        @click="activeTab = 1"
                        class="px-6 py-3 font-semibold transition-all duration-200 border-b-2 relative"
                        :class="{
                            'text-blue-600 border-blue-600': activeTab === 1,
                            'text-gray-500 border-transparent hover:text-gray-700': activeTab !== 1
                        }"
                    >
                        Solicitudes
                        <span
                            v-if="materialRequests && materialRequests.filter(r => r.status === 'pending').length > 0"
                            class="ml-2 px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full"
                        >
                            {{ materialRequests.filter(r => r.status === 'pending').length }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Tab 0: Materiales -->
            <BaseTable
                v-if="activeTab === 0"
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

            <!-- Tab 1: Solicitudes (solo admin) -->
            <BaseTable
                v-if="activeTab === 1 && isAdmin && materialRequests"
                :value="materialRequests"
                :columns="requestColumns"
                :loading="false"
                :actions-type="'none'"
                :paginator="true"
                :rows="10"
                :rows-per-page-options="[5, 10, 20]"
                :totalVisible="4"
                data-key="id"
                emptyMessage="No hay solicitudes de materiales"
                class="bg-white rounded-[15px] p-6"
            >
                <template #body-type_requested="{ data }">
                    <div
                        class="flex items-center gap-2 px-3 py-1.5 rounded-md w-fit"
                        :class="{
                            'text-red-500 bg-red-50': data.type_requested === 'video',
                            'text-red-600 bg-red-50': data.type_requested === 'pdf',
                            'text-blue-500 bg-blue-50': data.type_requested === 'link',
                            'text-gray-600 bg-gray-50': data.type_requested === 'document'
                        }"
                    >
                        <span class="capitalize font-medium text-sm">{{ getTypeLabel(data.type_requested) }}</span>
                    </div>
                </template>

                <template #body-requester="{ data }">
                    <span class="text-sm text-gray-700">
                        {{ data.requester.person.first_name }} {{ data.requester.person.last_name }}
                    </span>
                </template>

                <template #body-status="{ data }">
                    <span
                        class="px-3 py-1.5 rounded-full text-sm font-semibold"
                        :class="{
                            'bg-yellow-100 text-yellow-800': data.status === 'pending',
                            'bg-green-100 text-green-800': data.status === 'approved',
                            'bg-red-100 text-red-800': data.status === 'rejected'
                        }"
                    >
                        {{ getStatusLabel(data.status) }}
                    </span>
                </template>

                <template #body-created_at="{ data }">
                    <span class="text-sm text-gray-600">
                        {{ new Date(data.created_at).toLocaleDateString() }}
                    </span>
                </template>

                <template #body-actions="{ data }">
                    <div v-if="data.status === 'pending'" class="flex gap-2">
                        <button
                            @click="handleApproveRequest(data)"
                            class="px-3 py-1.5 text-sm font-semibold text-white bg-green-500 rounded-md hover:bg-green-600 transition-colors"
                        >
                            Aprobar
                        </button>
                        <button
                            @click="handleRejectRequest(data)"
                            class="px-3 py-1.5 text-sm font-semibold text-white bg-red-500 rounded-md hover:bg-red-600 transition-colors"
                        >
                            Rechazar
                        </button>
                    </div>
                    <span v-else class="text-sm text-gray-500">
                        {{ data.status === 'approved' ? 'Aprobada' : 'Rechazada' }}
                    </span>
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

        <MaterialReviewModal
            :isOpen="showReviewModal"
            :request="requestToReview"
            @close="showReviewModal = false; requestToReview = null"
            @approve="confirmApprove"
            @reject="confirmReject"
        />
    </AppLayout>
</template>
