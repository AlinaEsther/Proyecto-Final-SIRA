<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, BookOpen, ExternalLink, Calendar } from 'lucide-vue-next';

interface HistoryRecommendation {
    id: number;
    book_title: string;
    book_author?: string;
    course_name: string;
    status: string;
    created_at: string;
    material_id?: number;
}

interface Props {
    isOpen: boolean;
    recommendations: HistoryRecommendation[];
    isStudent: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
}>();

const handleClose = () => {
    emit('close');
};

const viewMaterial = (materialId: number) => {
    router.visit(`/materials/${materialId}`);
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        'not_requested': 'No Solicitada',
        'pending': 'Pendiente de Aprobación',
        'completed': 'Completada',
        'viewed': 'Vista',
        'dismissed': 'Descartada'
    };
    return labels[status] || status;
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'not_requested': 'bg-purple-100 text-purple-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'viewed': 'bg-blue-100 text-blue-800',
        'dismissed': 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="handleClose"
            >
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

                <!-- Modal -->
                <div class="flex min-h-screen items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="isOpen"
                            class="relative w-full max-w-4xl max-h-[90vh] bg-white rounded-2xl shadow-2xl flex flex-col"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 flex-shrink-0">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                                        <BookOpen :size="28" class="text-blue-600" />
                                        Historial de Recomendaciones
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ recommendations.length }} recomendación(es) generada(s)
                                    </p>
                                </div>
                                <button
                                    @click="handleClose"
                                    class="p-2 hover:bg-white/50 rounded-full transition-colors"
                                >
                                    <X :size="24" class="text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="p-6 flex-1 overflow-y-auto min-h-0">
                                <!-- Sin recomendaciones -->
                                <div v-if="recommendations.length === 0" class="text-center py-12">
                                    <BookOpen :size="64" class="mx-auto text-gray-300 mb-4" />
                                    <h4 class="text-lg font-semibold text-gray-700 mb-2">
                                        No hay recomendaciones aún
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        Genera tu primera recomendación para verla aquí
                                    </p>
                                </div>

                                <!-- Grid de recomendaciones -->
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div
                                        v-for="rec in recommendations"
                                        :key="rec.id"
                                        class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 hover:shadow-md transition-all border border-gray-200"
                                    >
                                        <!-- Header del card -->
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-start gap-3 flex-1">
                                                <div class="p-2 bg-blue-100 rounded-lg flex-shrink-0">
                                                    <BookOpen :size="20" class="text-blue-600" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-bold text-gray-900 mb-1 line-clamp-2">
                                                        {{ rec.book_title }}
                                                    </h4>
                                                    <p v-if="rec.book_author" class="text-sm text-gray-600 mb-2">
                                                        {{ rec.book_author }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span
                                                :class="['px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap ml-2', getStatusColor(rec.status)]"
                                            >
                                                {{ getStatusLabel(rec.status) }}
                                            </span>
                                        </div>

                                        <!-- Info del curso -->
                                        <div class="mb-3 pb-3 border-b border-gray-200">
                                            <p class="text-sm text-gray-700">
                                                <span class="font-medium">Curso:</span> {{ rec.course_name }}
                                            </p>
                                            <p class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                <Calendar :size="12" />
                                                {{ new Date(rec.created_at).toLocaleDateString('es-ES', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric'
                                                }) }}
                                            </p>
                                        </div>

                                        <!-- Acciones -->
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Botón para estudiantes -->
                                            <button
                                                v-if="isStudent && rec.material_id"
                                                @click="viewMaterial(rec.material_id)"
                                                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                                            >
                                                <ExternalLink :size="16" />
                                                Ver Material
                                            </button>
                                            <span
                                                v-else-if="isStudent && !rec.material_id"
                                                class="text-xs text-gray-500 italic"
                                            >
                                                Material no disponible
                                            </span>

                                            <!-- Botón para admin/profesor -->
                                            <button
                                                v-if="!isStudent"
                                                @click="router.visit('/materials')"
                                                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors"
                                            >
                                                <ExternalLink :size="16" />
                                                Gestionar Materiales
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                                <button
                                    @click="handleClose"
                                    class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                                >
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
