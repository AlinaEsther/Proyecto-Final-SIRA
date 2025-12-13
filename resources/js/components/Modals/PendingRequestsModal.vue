<script setup lang="ts">
import { X, Check, XCircle, ExternalLink, BookOpen, Calendar, User } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface MaterialRequest {
    id: number;
    title: string;
    author: string | null;
    type_requested: string;
    description: string;
    url: string;
    status: string;
    created_at: string;
    requester?: {
        person?: {
            full_name: string;
        };
        name: string;
    };
    recommendation?: {
        course_name: string;
    };
}

interface Props {
    isOpen: boolean;
    requests: MaterialRequest[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'review', id: number): void;
}>();

const closeModal = () => {
    emit('close');
};

const reviewRequest = (requestId: number) => {
    emit('close');
    router.visit('/materials');
};

const getStatusBadge = (status: string) => {
    const badges = {
        pending: { text: 'Pendiente', class: 'bg-yellow-100 text-yellow-800' },
        approved: { text: 'Aprobado', class: 'bg-green-100 text-green-800' },
        rejected: { text: 'Rechazado', class: 'bg-red-100 text-red-800' },
    };
    return badges[status as keyof typeof badges] || badges.pending;
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                @click.self="closeModal"
            >
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="isOpen"
                        class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col"
                    >
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-5 flex items-center justify-between flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/20 p-2 rounded-lg">
                                    <BookOpen :size="24" class="text-white" />
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-white">Solicitudes Pendientes</h2>
                                    <p class="text-white/90 text-sm mt-1">
                                        {{ requests.length }} {{ requests.length === 1 ? 'solicitud' : 'solicitudes' }} esperando revisión
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="closeModal"
                                class="text-white hover:bg-white/20 p-2 rounded-lg transition-colors"
                                aria-label="Cerrar"
                            >
                                <X :size="24" />
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 overflow-y-auto p-6 min-h-0">
                            <div v-if="requests.length === 0" class="text-center py-12">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                    <Check :size="32" class="text-gray-400" />
                                </div>
                                <p class="text-gray-600 text-lg font-medium">No hay solicitudes pendientes</p>
                                <p class="text-gray-500 text-sm mt-2">Todas las solicitudes han sido revisadas</p>
                            </div>

                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="request in requests"
                                    :key="request.id"
                                    class="border border-gray-200 rounded-xl p-5 hover:shadow-lg hover:border-orange-200 transition-all bg-white"
                                >
                                    <!-- Status Badge -->
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <BookOpen :size="18" class="text-orange-600" />
                                            <span
                                                :class="[
                                                    'px-2.5 py-1 rounded-full text-xs font-semibold',
                                                    getStatusBadge(request.status).class
                                                ]"
                                            >
                                                {{ getStatusBadge(request.status).text }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Title & Author -->
                                    <h3 class="font-bold text-gray-900 text-lg mb-1 line-clamp-2">
                                        {{ request.title }}
                                    </h3>
                                    <p v-if="request.author" class="text-sm text-gray-600 mb-3">
                                        por {{ request.author }}
                                    </p>

                                    <!-- Course -->
                                    <div v-if="request.recommendation?.course_name" class="flex items-center gap-2 mb-3">
                                        <div class="bg-blue-50 px-3 py-1.5 rounded-lg">
                                            <p class="text-xs font-semibold text-blue-700">
                                                {{ request.recommendation.course_name }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <p class="text-sm text-gray-700 mb-4 line-clamp-2">
                                        {{ request.description }}
                                    </p>

                                    <!-- Meta Info -->
                                    <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
                                        <div class="flex items-center gap-1">
                                            <Calendar :size="14" />
                                            <span>{{ new Date(request.created_at).toLocaleDateString('es-ES') }}</span>
                                        </div>
                                        <div v-if="request.requester" class="flex items-center gap-1">
                                            <User :size="14" />
                                            <span>{{ request.requester.person?.full_name || request.requester.name }}</span>
                                        </div>
                                    </div>

                                    <!-- URL Preview -->
                                    <a
                                        v-if="request.url && request.url !== '#'"
                                        :href="request.url"
                                        target="_blank"
                                        class="flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 hover:underline mb-4"
                                    >
                                        <ExternalLink :size="14" />
                                        <span class="truncate">{{ request.url }}</span>
                                    </a>

                                    <!-- Action Button -->
                                    <button
                                        @click="reviewRequest(request.id)"
                                        class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-2.5 px-4 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 font-medium text-sm flex items-center justify-center gap-2 shadow-md hover:shadow-lg"
                                    >
                                        <Check :size="16" />
                                        Revisar Solicitud
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                            <p class="text-sm text-gray-600">
                                Revisa y aprueba las solicitudes en la sección de <strong>Materiales</strong>
                            </p>
                            <button
                                @click="closeModal"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors font-medium text-sm"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
