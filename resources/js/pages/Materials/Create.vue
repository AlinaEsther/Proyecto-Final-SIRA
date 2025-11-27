<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Save, X, Upload, File, FileText, Trash2 } from 'lucide-vue-next';
import BaseSelect from '@/components/BaseSelect.vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Materiales', href: '/materials' },
    { title: 'Nuevo Material', href: '/materials/create' }
];

const form = useForm({
    title: '',
    type: 'document',
    description: '',
    url: '',
    file: null as File | null,
});

const fileInputRef = ref<HTMLInputElement | null>(null);
const selectedFileName = ref<string>('');
const selectedFileSize = ref<string>('');
const isDragging = ref(false);

const typeOptions = [
    { id: 'document', name: 'Documento' },
    { id: 'pdf', name: 'PDF' },
    { id: 'video', name: 'Video' },
    { id: 'link', name: 'Enlace' }
];

const submit = () => {
    form.post(route('materials.store'), {
        preserveScroll: true,
    });
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        processFile(file);
    }
};

const processFile = (file: File) => {
    form.file = file;
    selectedFileName.value = file.name;
    selectedFileSize.value = formatFileSize(file.size);
};

const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;

    const files = e.dataTransfer?.files;
    if (files && files[0]) {
        processFile(files[0]);
    }
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const removeFile = () => {
    form.file = null;
    selectedFileName.value = '';
    selectedFileSize.value = '';
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const cancelForm = () => {
    router.visit(route('materials.index'));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <!-- Encabezado -->
            <div class="p-6 border-b bg-white rounded-[15px] pb-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Nuevo Material</h1>
                        <p class="text-gray-500 mt-1">Crea un nuevo material de estudio</p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="p-6 border-b bg-white rounded-[15px]">
                <!-- Sección 1: Título y Tipo - 2 campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Título -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Título<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: Guía de Laravel"
                            :class="{ 'border-red-500': form.errors.title }"
                            required
                        />
                        <span v-if="form.errors.title" class="text-red-500 text-xs">{{ form.errors.title }}</span>
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo<span class="text-red-600">*</span>
                        </label>
                        <BaseSelect
                            v-model="form.type"
                            :options="typeOptions"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Seleccione un tipo"
                            entity="tipos"
                            :filter="false"
                            :class="{ 'border-red-500': form.errors.type }"
                        />
                        <span v-if="form.errors.type" class="text-red-500 text-xs">{{ form.errors.type }}</span>
                    </div>
                </div>

                <!-- Sección 2: URL o Archivo - condicional -->
                <div v-if="form.type === 'link'" class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL<span class="text-red-600">*</span>
                        </label>
                        <input
                            v-model="form.url"
                            type="url"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://..."
                            :class="{ 'border-red-500': form.errors.url }"
                            required
                        />
                        <span v-if="form.errors.url" class="text-red-500 text-xs">{{ form.errors.url }}</span>
                        <p class="text-xs text-gray-500 mt-1">Ingrese la URL del enlace</p>
                    </div>
                </div>

                <!-- Video: URL o Archivo -->
                <div v-if="form.type === 'video'" class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL del Video
                        </label>
                        <input
                            v-model="form.url"
                            type="url"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://youtube.com/watch?v=... o https://vimeo.com/..."
                            :class="{ 'border-red-500': form.errors.url }"
                        />
                        <span v-if="form.errors.url" class="text-red-500 text-xs">{{ form.errors.url }}</span>
                        <p class="text-xs text-gray-500 mt-1">Ingrese la URL del video de YouTube, Vimeo u otra plataforma</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex-1 border-t border-gray-300"></div>
                        <span class="text-sm text-gray-500 font-medium">O</span>
                        <div class="flex-1 border-t border-gray-300"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Subir Archivo de Video
                        </label>

                        <!-- Input oculto -->
                        <input
                            ref="fileInputRef"
                            type="file"
                            @change="handleFileChange"
                            class="hidden"
                            accept="video/*,.mp4,.avi,.mov,.wmv,.flv,.mkv"
                        />

                        <!-- Área de carga personalizada -->
                        <div v-if="!selectedFileName"
                            @click="triggerFileInput"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer transition-all duration-200 hover:border-blue-500 hover:bg-blue-50/50"
                            :class="{
                                'border-red-500 bg-red-50/30': form.errors.file,
                                'border-blue-500 bg-blue-100/50 scale-[1.02]': isDragging
                            }"
                        >
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-3 bg-blue-100 rounded-full" :class="{ 'bg-blue-200': isDragging }">
                                    <Upload class="h-8 w-8 text-blue-600" :class="{ 'animate-bounce': isDragging }" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">
                                        {{ isDragging ? '¡Suelta el archivo aquí!' : 'Haz clic para seleccionar un video' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ isDragging ? '' : 'o arrastra y suelta aquí' }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400">
                                    MP4, AVI, MOV, WMV, FLV, MKV (máx. 100MB)
                                </p>
                            </div>
                        </div>

                        <!-- Archivo seleccionado -->
                        <div v-else
                            class="border-2 border-green-300 bg-green-50/30 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0 p-2 bg-green-100 rounded-lg">
                                        <Video class="h-6 w-6 text-red-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate" :title="selectedFileName">
                                            {{ selectedFileName }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ selectedFileSize }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile"
                                    class="flex-shrink-0 p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                    title="Eliminar archivo"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <span v-if="form.errors.file" class="text-red-500 text-xs mt-1 block">{{ form.errors.file }}</span>
                        <p v-else class="text-xs text-gray-500 mt-1">Tamaño máximo: 100MB. Formatos aceptados: MP4, AVI, MOV, WMV, FLV, MKV</p>
                    </div>
                </div>

                <!-- PDF -->
                <div v-if="form.type === 'pdf'" class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Archivo PDF<span class="text-red-600">*</span>
                        </label>

                        <!-- Input oculto -->
                        <input
                            ref="fileInputRef"
                            type="file"
                            @change="handleFileChange"
                            class="hidden"
                            accept=".pdf"
                        />

                        <!-- Área de carga personalizada -->
                        <div v-if="!selectedFileName"
                            @click="triggerFileInput"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer transition-all duration-200 hover:border-blue-500 hover:bg-blue-50/50"
                            :class="{
                                'border-red-500 bg-red-50/30': form.errors.file,
                                'border-blue-500 bg-blue-100/50 scale-[1.02]': isDragging
                            }"
                        >
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-3 bg-blue-100 rounded-full" :class="{ 'bg-blue-200': isDragging }">
                                    <Upload class="h-8 w-8 text-blue-600" :class="{ 'animate-bounce': isDragging }" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">
                                        {{ isDragging ? '¡Suelta el archivo aquí!' : 'Haz clic para seleccionar un PDF' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ isDragging ? '' : 'o arrastra y suelta aquí' }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400">
                                    Solo PDF (máx. 25MB)
                                </p>
                            </div>
                        </div>

                        <!-- Archivo seleccionado -->
                        <div v-else
                            class="border-2 border-green-300 bg-green-50/30 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0 p-2 bg-green-100 rounded-lg">
                                        <FileText class="h-6 w-6 text-red-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate" :title="selectedFileName">
                                            {{ selectedFileName }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ selectedFileSize }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile"
                                    class="flex-shrink-0 p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                    title="Eliminar archivo"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <span v-if="form.errors.file" class="text-red-500 text-xs mt-1 block">{{ form.errors.file }}</span>
                        <p v-else class="text-xs text-gray-500 mt-1">Tamaño máximo: 25MB. Solo archivos PDF</p>
                    </div>
                </div>

                <!-- Documentos: Zona genérica -->
                <div v-if="form.type === 'document'" class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Archivo<span class="text-red-600">*</span>
                        </label>

                        <!-- Input oculto -->
                        <input
                            ref="fileInputRef"
                            type="file"
                            @change="handleFileChange"
                            class="hidden"
                            accept=".pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif"
                        />

                        <!-- Área de carga personalizada -->
                        <div v-if="!selectedFileName"
                            @click="triggerFileInput"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer transition-all duration-200 hover:border-blue-500 hover:bg-blue-50/50"
                            :class="{
                                'border-red-500 bg-red-50/30': form.errors.file,
                                'border-blue-500 bg-blue-100/50 scale-[1.02]': isDragging
                            }"
                        >
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-3 bg-blue-100 rounded-full" :class="{ 'bg-blue-200': isDragging }">
                                    <Upload class="h-8 w-8 text-blue-600" :class="{ 'animate-bounce': isDragging }" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">
                                        {{ isDragging ? '¡Suelta el archivo aquí!' : 'Haz clic para seleccionar un archivo' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ isDragging ? '' : 'o arrastra y suelta aquí' }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400">
                                    PDF, DOC, XLS, PPT, TXT, Imágenes (máx. 25MB)
                                </p>
                            </div>
                        </div>

                        <!-- Archivo seleccionado -->
                        <div v-else
                            class="border-2 border-green-300 bg-green-50/30 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0 p-2 bg-green-100 rounded-lg">
                                        <File class="h-6 w-6 text-blue-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate" :title="selectedFileName">
                                            {{ selectedFileName }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ selectedFileSize }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removeFile"
                                    class="flex-shrink-0 p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                    title="Eliminar archivo"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <span v-if="form.errors.file" class="text-red-500 text-xs mt-1 block">{{ form.errors.file }}</span>
                        <p v-else class="text-xs text-gray-500 mt-1">Tamaño máximo: 25MB. Formatos aceptados: PDF, DOC, XLS, PPT, TXT, Imágenes</p>
                    </div>
                </div>

                <!-- Sección 3: Descripción - 1 campo completo -->
                <div class="grid grid-cols-1 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="text-gray-900 text-sm rounded-lg block w-full p-2.5 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Describe el contenido del material..."
                            :class="{ 'border-red-500': form.errors.description }"
                        ></textarea>
                        <span v-if="form.errors.description" class="text-red-500 text-xs">{{ form.errors.description }}</span>
                        <p class="text-xs text-gray-500 mt-1">Proporciona una breve descripción del material (opcional)</p>
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
    </AppLayout>
</template>
