<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { FileText, Link as LinkIcon, Video, File, Download, ExternalLink } from 'lucide-vue-next';

interface Material {
    id: number;
    title: string;
    type: 'video' | 'pdf' | 'link' | 'document';
    file_path: string | null;
    original_filename: string | null;
    url: string | null;
    description: string | null;
    created_at: string;
}

const props = defineProps<{
    material: Material;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Materiales', href: '/materials' },
    { title: props.material.title, href: `/materials/${props.material.id}` }
];

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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getFileUrl = (): string | undefined => {
    if (props.material.url) {
        return props.material.url;
    }
    if (props.material.file_path) {
        return `/storage/${props.material.file_path}`;
    }
    return undefined;
};

const getDownloadUrl = (): string | undefined => {
    // Si tiene archivo local, usar la ruta de descarga del controlador
    if (props.material.file_path) {
        return route('materials.download', props.material.id);
    }
    // Si es URL externa, usar la URL directamente
    if (props.material.url) {
        return props.material.url;
    }
    return undefined;
};

const isExternalUrl = () => {
    if (!props.material.url) return false;
    try {
        const url = new URL(props.material.url);
        return url.host !== window.location.host;
    } catch {
        return false;
    }
};

</script>

<template>
    <Head :title="`Material - ${material.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <!-- Header -->
            <div class="p-6 border-b bg-white rounded-[15px]">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ material.title }}</h1>
                        <p class="text-gray-500 mt-1">Detalles del material</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <Link
                            :href="route('materials.edit', material.id)"
                            class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-all duration-300 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1]"
                        >
                            <span>Editar</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Información general -->
            <div class="p-6 bg-white rounded-[15px]">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Información General</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Tipo de Material</p>
                        <div class="flex items-center gap-2">
                            <span
                                class="px-3 py-2 inline-flex items-center gap-2 text-sm font-semibold rounded-lg"
                                :class="typeColors[material.type]"
                            >
                                <component :is="typeIcons[material.type]" class="h-5 w-5" />
                                {{ typeLabels[material.type] }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Fecha de Creación</p>
                        <p class="font-medium text-gray-800">{{ formatDate(material.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            <div class="p-6 bg-white rounded-[15px]">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Descripción</h3>
                <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-line">
                        {{ material.description || 'Sin descripción disponible' }}
                    </p>
                </div>
            </div>

            <!-- Acceso al material -->
            <div class="p-6 bg-white rounded-[15px]">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Acceso al Material</h3>

                <!-- Video -->
                <div v-if="material.type === 'video'" class="space-y-4">
                    <!-- Video desde URL -->
                    <div v-if="material.url" class="space-y-4">
                        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                            <iframe
                                v-if="material.url.includes('youtube.com') || material.url.includes('youtu.be')"
                                :src="material.url.replace('watch?v=', 'embed/')"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                            <video v-else :src="material.url" controls class="w-full h-full"></video>
                        </div>
                        <a
                            :href="material.url"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            <ExternalLink class="h-5 w-5" />
                            Abrir en nueva pestaña
                        </a>
                    </div>
                    <!-- Video desde archivo -->
                    <div v-else-if="material.file_path" class="space-y-4">
                        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                            <video :src="getFileUrl()" controls class="w-full h-full"></video>
                        </div>
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                            <Video class="h-12 w-12 text-red-600" />
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ material.original_filename || material.title }}</p>
                                <p class="text-sm text-gray-500">Video</p>
                            </div>
                            <a
                                :href="getDownloadUrl()"
                                download
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                            >
                                <Download class="h-5 w-5" />
                                Descargar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- PDF -->
                <div v-else-if="material.type === 'pdf' && getFileUrl()" class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <FileText class="h-12 w-12 text-red-600" />
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ material.original_filename || material.title }}</p>
                            <p class="text-sm text-gray-500">PDF</p>
                            <p v-if="isExternalUrl()" class="text-xs text-blue-500 mt-1 break-all">{{ material.url }}</p>
                        </div>
                        <!-- URL externa: abrir en nueva pestaña -->
                        <a
                            v-if="isExternalUrl()"
                            :href="getDownloadUrl()"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            <ExternalLink class="h-5 w-5" />
                            Abrir
                        </a>
                        <!-- Archivo local: descargar -->
                        <a
                            v-else
                            :href="getDownloadUrl()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            <Download class="h-5 w-5" />
                            Descargar
                        </a>
                    </div>
                </div>

                <!-- Document -->
                <div v-else-if="material.type === 'document' && getFileUrl()" class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <File class="h-12 w-12 text-gray-600" />
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ material.original_filename || material.title }}</p>
                            <p class="text-sm text-gray-500">Documento</p>
                            <p v-if="isExternalUrl()" class="text-xs text-blue-500 mt-1 break-all">{{ material.url }}</p>
                        </div>
                        <!-- URL externa: abrir en nueva pestaña -->
                        <a
                            v-if="isExternalUrl()"
                            :href="getDownloadUrl()"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            <ExternalLink class="h-5 w-5" />
                            Abrir
                        </a>
                        <!-- Archivo local: descargar -->
                        <a
                            v-else
                            :href="getDownloadUrl()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            <Download class="h-5 w-5" />
                            Descargar
                        </a>
                    </div>
                </div>

                <!-- Link -->
                <div v-else-if="material.type === 'link' && material.url" class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <LinkIcon class="h-12 w-12 text-blue-600" />
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ material.title }}</p>
                            <p class="text-sm text-gray-500 break-all">{{ material.url }}</p>
                        </div>
                        <a
                            :href="material.url"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            <ExternalLink class="h-5 w-5" />
                            Visitar
                        </a>
                    </div>
                </div>

                <div v-else class="text-center text-gray-500 py-8">
                    <p>No hay contenido disponible para este material</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
