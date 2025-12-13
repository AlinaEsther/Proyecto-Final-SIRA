<script setup lang="ts">
import { ref, watch } from 'vue';
import { X, Check, XCircle } from 'lucide-vue-next';

interface MaterialRequest {
    id: number;
    title: string;
    author: string | null;
    type_requested: 'video' | 'pdf' | 'link' | 'document';
    description: string | null;
    url: string | null;
    status: 'pending' | 'approved' | 'rejected';
}

interface Props {
    isOpen: boolean;
    request: MaterialRequest | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'approve', data: { title: string; author: string; url: string; description: string }): void;
    (e: 'reject', notes: string): void;
}>();

const editableTitle = ref('');
const editableAuthor = ref('');
const editableUrl = ref('');
const editableDescription = ref('');
const adminNotes = ref('');

watch(() => props.request, (newRequest) => {
    if (newRequest) {
        editableTitle.value = newRequest.title || '';
        editableAuthor.value = newRequest.author || '';
        editableUrl.value = newRequest.url || '';
        editableDescription.value = newRequest.description || '';
        adminNotes.value = '';
    }
}, { immediate: true });

const handleApprove = () => {
    emit('approve', {
        title: editableTitle.value,
        author: editableAuthor.value,
        url: editableUrl.value,
        description: editableDescription.value
    });
};

const handleReject = () => {
    emit('reject', adminNotes.value);
};

const handleClose = () => {
    emit('close');
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
                v-if="isOpen && request"
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
                            class="relative w-full max-w-2xl max-h-[90vh] bg-white rounded-2xl shadow-2xl flex flex-col"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">
                                        Revisar Solicitud de Material
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Revisa y edita los detalles antes de aprobar
                                    </p>
                                </div>
                                <button
                                    @click="handleClose"
                                    class="p-2 hover:bg-gray-100 rounded-full transition-colors"
                                >
                                    <X :size="24" class="text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="p-6 space-y-4 flex-1 overflow-y-auto min-h-0">
                                <!-- Título -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Título del Material *
                                    </label>
                                    <input
                                        v-model="editableTitle"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Título del material"
                                    />
                                </div>

                                <!-- Autor -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Autor
                                    </label>
                                    <input
                                        v-model="editableAuthor"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Autor del material"
                                    />
                                </div>

                                <!-- URL -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        URL *
                                    </label>
                                    <input
                                        v-model="editableUrl"
                                        type="url"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="https://..."
                                    />
                                    <p v-if="editableUrl && editableUrl !== '#'" class="text-xs text-gray-500 mt-1">
                                        <a :href="editableUrl" target="_blank" rel="noopener" class="text-blue-600 hover:underline">
                                            Ver enlace →
                                        </a>
                                    </p>
                                </div>

                                <!-- Descripción -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Descripción
                                    </label>
                                    <textarea
                                        v-model="editableDescription"
                                        rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        placeholder="Descripción del material..."
                                    ></textarea>
                                </div>

                                <!-- Tipo -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Tipo de Material
                                    </label>
                                    <div class="px-4 py-2 bg-gray-100 rounded-lg text-gray-700">
                                        {{ request.type_requested === 'link' ? 'Enlace' :
                                           request.type_requested === 'pdf' ? 'PDF' :
                                           request.type_requested === 'video' ? 'Video' : 'Documento' }}
                                    </div>
                                </div>

                                <!-- Notas de Admin (para rechazo) -->
                                <div class="pt-4 border-t border-gray-200">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Notas Administrativas (opcional)
                                    </label>
                                    <textarea
                                        v-model="adminNotes"
                                        rows="2"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        placeholder="Razón de aprobación o rechazo..."
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl flex-shrink-0">
                                <button
                                    @click="handleClose"
                                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button
                                    @click="handleReject"
                                    class="flex items-center gap-2 px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                                >
                                    <XCircle :size="18" />
                                    Rechazar
                                </button>
                                <button
                                    @click="handleApprove"
                                    :disabled="!editableTitle || !editableUrl"
                                    class="flex items-center gap-2 px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
                                >
                                    <Check :size="18" />
                                    Aprobar y Crear Material
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
