<script setup lang="ts">
import type { BreadcrumbItemType } from '@/types';
import { router as inertiaRouter, Link, usePage } from '@inertiajs/vue3';
import { DropdownMenuContent, DropdownMenuItem, DropdownMenuPortal, DropdownMenuRoot, DropdownMenuTrigger } from 'radix-vue';
import { ref, computed } from 'vue';
import { ChevronDown, Bell } from 'lucide-vue-next';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage()

const user = computed(() => page.props.auth.user)
const appName = computed(() => page.props.name)

// Función para construir la URL de la imagen de perfil
const getProfilePictureUrl = computed(() => {
    const profilePicturePath = user.value.person?.profile_picture;

    if (profilePicturePath) {
        // Si ya es una ruta absoluta que empieza con /images/ (como viene del seeder)
        if (profilePicturePath.startsWith('/images/')) {
            return profilePicturePath; // Usar tal como está
        }
        // Si ya es una ruta absoluta (empieza con http o /storage)
        if (profilePicturePath.startsWith('http') || profilePicturePath.startsWith('/storage')) {
            return profilePicturePath;
        }
        // Si empieza con images/ (sin slash inicial), agregar /storage/
        if (profilePicturePath.startsWith('images/')) {
            return `/storage/${profilePicturePath}`;
        }
        // Si no tiene prefijo, asumir que está en storage/images
        return `/storage/images/${profilePicturePath}`;
    }

    // Si no hay imagen, usar default
    return '/images/default.png';
});

// Función para manejar errores de carga de imagen
const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    const currentSrc = img.src;
    const originalPath = user.value.person?.profile_picture;

    // Si falló la carga desde /images/ (public), intentar desde storage
    if (currentSrc.includes('/images/') && !currentSrc.includes('/storage/') && originalPath) {
        if (originalPath.startsWith('/images/')) {
            const filename = originalPath.replace('/images/', '');
            const storageUrl = `/storage/images/${filename}`;

            if (!currentSrc.includes('/storage/')) {
                img.src = storageUrl;
                return;
            }
        }
    }

    // Si falló la carga desde storage, intentar desde public/images
    if (currentSrc.includes('/storage/') && originalPath) {
        const filename = originalPath.split('/').pop() || originalPath;
        const fallbackUrl = `/images/${filename}`;

        if (!currentSrc.includes('/images/') || currentSrc.includes('/storage/')) {
            img.src = fallbackUrl;
            return;
        }
    }

    // Si también falla, usar imagen por defecto
    if (!currentSrc.includes('/images/default.png')) {
        img.src = '/images/default.png';
    }
};

// console.log(user.value.person?.profile_picture);
const profileItems = ref([
    { name: 'Ver Perfil', path: '/settings/profile', method: 'get' },
    { name: 'Cambiar contraseña', path: '/settings/password', method: 'get' },
    { name: 'Cerrar sesión', path: '/logout', method: 'post' },
]);
</script>

<template>
    <div class="flex items-center justify-between p-4 text-white border-b border-gray-300 px-0 mb-4">
        <!-- Titulo en el lado izquierdo con el doble de ancho -->
        <div class="flex items-center space-x-4">
            <div class="relative w-full">
                <p class="text-3xl font-medium text-[#121212]">
                    {{ appName || 'Sistema Inteligente de Recomendaciones Académicas' }}
                </p>
            </div>
        </div>
        <!-- Elementos del header en el lado derecho -->
        <div class="flex items-center space-x-6">

            <!-- Icono de notificación con campana -->
            <DropdownMenuRoot>
                <DropdownMenuTrigger>
                    <button class="relative p-2 rounded-xl text-[#1e3a8a] transition-colors duration-300">
                        <Bell></Bell>
                        <span class="absolute right-0.5 top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                            3
                        </span>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuPortal>
                    <DropdownMenuContent align="end" :sideOffset="8" :positioning="{ strategy: 'fixed' }" class="z-50 mt-2 min-w-[12rem] w-[60vw] max-w-[90vw] sm:w-[20rem] rounded-lg bg-white text-black shadow-lg">
                        <DropdownMenuItem class="cursor-pointer rounded p-2 hover:bg-gray-200">
                            No tienes notificaciones sin leer
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenuPortal>
            </DropdownMenuRoot>

            <!-- Dropdown de perfil del usuario -->
            <DropdownMenuRoot>
                <DropdownMenuTrigger as-child>
                    <button class="flex items-center space-x-2 rounded-lg relative p-2 text-blue-900">
                        <div id="profile-picture-wrap">
                            <img
                                :src="getProfilePictureUrl"
                                alt="Usuario"
                                @error="handleImageError"
                            />
                        </div>

                        <span>{{ user.name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </DropdownMenuTrigger>


                <DropdownMenuPortal>
                    <DropdownMenuContent align="end" :sideOffset="8" :positioning="{ strategy: 'fixed' }" class="z-50 mt-2 min-w-[10rem] w-[50vw] max-w-[90vw] sm:w-[16rem] rounded-lg bg-white text-black shadow-lg">
                        <DropdownMenuItem
                            v-for="(item, index) in profileItems"
                            :key="index"
                            class="cursor-pointer rounded p-2 hover:bg-gray-200"
                            @select="inertiaRouter.visit(item.path, { method: item.method })"
                        >
                            {{ item.name }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenuPortal>
            </DropdownMenuRoot>
        </div>
    </div>

</template>

<style>
#profile-picture-wrap{
    width: 50px;
    height: 50px;
    border-radius: 100px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ccc;
    img{
        height: inherit!important;
        max-width: inherit!important;
    }
}

</style>
