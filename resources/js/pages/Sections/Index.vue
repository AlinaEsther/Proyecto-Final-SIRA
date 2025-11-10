<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { BreadcrumbItem } from '@/types';

interface FilterType {
    course_id?: string;
    academic_period?: string;
    status?: string;
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

const filters = ref<FilterType>({
    course_id: props.filters?.course_id || '',
    academic_period: props.filters?.academic_period || '',
    status: props.filters?.status || '',
});

const applyFilters = () => {
    router.get(route('sections.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { course_id: '', academic_period: '', status: '' };
    applyFilters();
};

const deleteSection = (id: number, name: string) => {
    if (confirm(`¿Estás seguro de que deseas eliminar la sección "${name}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('sections.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                // Inertia automáticamente recargará la página después de eliminar
            },
        });
    }
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

            <!-- Filtros -->
            <div class="p-6 bg-white rounded-[15px]">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Curso</label>
                        <input
                            v-model="filters.course_id"
                            type="text"
                            class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="ID del curso"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periodo</label>
                        <input
                            v-model="filters.academic_period"
                            type="text"
                            class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="2025-1"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select
                            v-model="filters.status"
                            class="w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Todos</option>
                            <option value="open">Abierto</option>
                            <option value="closed">Cerrado</option>
                            <option value="completed">Completado</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            @click="applyFilters"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition"
                        >
                            Filtrar
                        </button>
                        <button
                            @click="clearFilters"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded shadow transition"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lista de secciones -->
            <div class="p-6 bg-white rounded-[15px]">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Curso
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sección
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Profesor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Periodo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="!sections?.data || sections.data.length === 0">
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No hay secciones registradas todavía
                            </td>
                        </tr>
                        <tr v-for="section in sections?.data" :key="section.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ section.course.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ section.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ section.professor?.person?.full_name || section.professor?.name || 'Sin asignar' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ section.academic_period }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-green-100 text-green-800': section.status === 'open',
                                        'bg-red-100 text-red-800': section.status === 'closed',
                                        'bg-gray-100 text-gray-800': section.status === 'completed',
                                    }"
                                >
                                    {{ section.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link
                                    :href="route('sections.show', section.id)"
                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                >
                                    Ver
                                </Link>
                                <Link
                                    :href="route('sections.edit', section.id)"
                                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                                >
                                    Editar
                                </Link>
                                <button
                                    @click="deleteSection(section.id, section.name)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="sections?.data && sections.data.length > 0" class="mt-4 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Mostrando {{ sections.from }} a {{ sections.to }} de {{ sections.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="(link, index) in sections.links"
                            :key="index"
                            :href="link.url || undefined"
                            :class="{
                                'bg-blue-600 text-white': link.active,
                                'bg-gray-200 text-gray-700 hover:bg-gray-300': !link.active && link.url,
                                'cursor-not-allowed opacity-50': !link.url,
                            }"
                            class="px-3 py-1 rounded shadow-sm transition"
                        >
                            <span v-html="link.label" />
                        </component>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
