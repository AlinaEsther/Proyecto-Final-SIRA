<script setup lang="ts">
import { ref, computed } from 'vue';
import { X, Search, FileText, Video, Link as LinkIcon, File } from 'lucide-vue-next';

interface Material {
    id: number;
    title: string;
    type: 'video' | 'pdf' | 'link' | 'document';
    description?: string | null;
}

interface AttachedMaterial {
    id: number;
    is_required: boolean;
}

interface Props {
    isOpen: boolean;
    materials: Material[];
    attachedMaterials?: AttachedMaterial[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    save: [materials: AttachedMaterial[]];
}>();

const searchQuery = ref('');
const selectedMaterials = ref<Map<number, AttachedMaterial>>(new Map());

// Inicializar con materiales ya adjuntados
if (props.attachedMaterials) {
    props.attachedMaterials.forEach(mat => {
        selectedMaterials.value.set(mat.id, { ...mat });
    });
}

const filteredMaterials = computed(() => {
    if (!searchQuery.value) return props.materials;
    return props.materials.filter(m =>
        m.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
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

const typeLabels = {
    video: 'Video',
    pdf: 'PDF',
    link: 'Enlace',
    document: 'Documento'
};

const isMaterialSelected = (materialId: number) => {
    return selectedMaterials.value.has(materialId);
};

const toggleMaterial = (material: Material) => {
    if (selectedMaterials.value.has(material.id)) {
        selectedMaterials.value.delete(material.id);
    } else {
        selectedMaterials.value.set(material.id, {
            id: material.id,
            is_required: false
        });
    }
};

const toggleRequired = (materialId: number) => {
    const material = selectedMaterials.value.get(materialId);
    if (material) {
        material.is_required = !material.is_required;
        selectedMaterials.value.set(materialId, { ...material });
    }
};

const handleSave = () => {
    const materialsArray = Array.from(selectedMaterials.value.values());
    emit('save', materialsArray);
    handleClose();
};

const handleClose = () => {
    emit('close');
};

const selectedCount = computed(() => selectedMaterials.value.size);
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="handleClose"
            >
                <div class="flex min-h-full items-center justify-center p-4">
                    <!-- Overlay -->
                    <div class="fixed inset-0 bg-black/50 transition-opacity"></div>

                    <!-- Modal -->
                    <div class="relative bg-white rounded-[15px] shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-6 border-b">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Gestionar Materiales</h2>
                                <p class="text-sm text-gray-600 mt-1">
                                    Selecciona los materiales y marca si son requeridos
                                </p>
                            </div>
                            <button
                                @click="handleClose"
                                class="text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <X class="h-6 w-6" />
                            </button>
                        </div>

                        <!-- Search -->
                        <div class="p-6 border-b">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Buscar materiales..."
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500 text-sm"
                                />
                            </div>
                            <div class="mt-3 text-sm text-gray-600">
                                <span class="font-medium">{{ selectedCount }}</span> material{{ selectedCount !== 1 ? 'es' : '' }} seleccionado{{ selectedCount !== 1 ? 's' : '' }}
                            </div>
                        </div>

                        <!-- Materials List -->
                        <div class="flex-1 overflow-y-auto p-6">
                            <div v-if="filteredMaterials.length === 0" class="text-center py-8 text-gray-500">
                                No se encontraron materiales
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="material in filteredMaterials"
                                    :key="material.id"
                                    class="flex items-center gap-4 p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                                    :class="{
                                        'bg-blue-50 border-blue-300': isMaterialSelected(material.id),
                                        'border-gray-200': !isMaterialSelected(material.id)
                                    }"
                                >
                                    <!-- Checkbox para seleccionar -->
                                    <input
                                        type="checkbox"
                                        :checked="isMaterialSelected(material.id)"
                                        @change="toggleMaterial(material)"
                                        class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                    />

                                    <!-- Icon + Info -->
                                    <div class="flex-1 flex items-center gap-3">
                                        <span
                                            class="px-3 py-2 inline-flex items-center gap-2 text-sm font-semibold rounded-lg"
                                            :class="typeColors[material.type]"
                                        >
                                            <component :is="typeIcons[material.type]" class="h-4 w-4" />
                                            {{ typeLabels[material.type] }}
                                        </span>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ material.title }}</p>
                                            <p v-if="material.description" class="text-sm text-gray-600 truncate">
                                                {{ material.description }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Required Toggle -->
                                    <div
                                        v-if="isMaterialSelected(material.id)"
                                        class="flex items-center gap-2"
                                    >
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :checked="selectedMaterials.get(material.id)?.is_required"
                                                @change="toggleRequired(material.id)"
                                                class="sr-only peer"
                                            />
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            <span class="ms-3 text-sm font-medium text-gray-700">
                                                {{ selectedMaterials.get(material.id)?.is_required ? 'Requerido' : 'Opcional' }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-4 p-6 border-t bg-gray-50">
                            <button
                                @click="handleClose"
                                class="flex items-center gap-2 rounded-full px-4 py-2 font-semibold text-white transition-transform duration-200 hover:scale-[1.05] focus:scale-[1] bg-gray-500/75 hover:bg-gray-600"
                            >
                                <X class="h-5 w-5"/>
                                <span>Cancelar</span>
                            </button>
                            <button
                                @click="handleSave"
                                class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-transform duration-200 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1]"
                            >
                                <span>Guardar Cambios</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.9);
}
</style>
