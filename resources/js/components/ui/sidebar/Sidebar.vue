<script setup lang="ts">
import { computed, ref, watch, nextTick } from 'vue';
import type { Component } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    ChevronDown,
    Circle,
    ClipboardList,
    House,
    Landmark,
    LayoutGrid,
    ListChecks,
    Settings,
    Bookmark,
} from 'lucide-vue-next';

// Types
interface MenuSubItem {
    name: string;
    path: string;
    roles?: string[];
    target?: string;
}

interface MenuItem {
    name: string;
    path: string;
    icon?: Component | any;
    roles?: string[];
    target?: string;
    subMenu?: MenuSubItem[];
}

interface Auth {
    user: any;
    roles: string[];
}

const menuItems = ref<MenuItem[]>([
    {
        name: 'Inicio',
        path: '/dashboard',
        icon: House,
    },
    {
        name: 'Secciones',
        path: '/sections',
        icon: LayoutGrid,
        subMenu: [
            {name: '1A', path: '/sections/1b'},
            {name: '2B', path: '/sections/2b'},
            {name: '3B', path: '/sections/3b'},
        ]
    },
    {
        name: 'Cursos',
        path: '/courses',
        icon: Landmark,
    },
    {
        name: 'Materiales',
        path: '/materials',
        icon: Bookmark,
    },
    {
        name: 'Rendimiento Academico',
        path: '/academic-perfomance',
        icon: ListChecks,
    },
    {
        name: 'Recomendaciones',
        path: '/recommendations',
        icon: ClipboardList,
    },
    {
        name: 'Configuración', path: '/settings', icon: Settings,
        subMenu: [
            { name: 'Notificaciones', path: '/configuration', roles: ['Administrador del sistema', 'super-admin', 'Administrador'] },
            { name: 'Mantenimientos', path: '/manage', roles: ['Administrador del sistema', 'super-admin',], target: '_blank' },
        ]
    },
    {
        name: 'Logs del Sistema',
        path: '/activity-logs',
        icon: ClipboardList,
        roles: ['Administrador del sistema']
    },
]);

const openSubmenus = ref<Set<number>>(new Set());
const toggleSubmenu = (index: number) => {
    if (openSubmenus.value.has(index)) {
        openSubmenus.value.delete(index);
    } else {
        openSubmenus.value.add(index);
    }
};

const isSubmenuOpen = (index: number) => {
    return openSubmenus.value.has(index);
};

const page = usePage();
const user = page.props.auth.user;
const roles = Array.isArray((page.props.auth as any).roles) ? (page.props.auth as any).roles : [];

function hasRole(itemRoles: string[] = []) {
    if (!itemRoles || itemRoles.length === 0) return true;
    if (!user || roles.length === 0) return false;
    return itemRoles.some(r => roles.includes(r));
}

function notNull<T>(v: T | null | undefined): v is T { return v != null; }

const filteredMenu = computed<MenuItem[]>(() => {
    return menuItems.value
        .filter(item => hasRole(item.roles))
        .map((item) => {
            if (item.subMenu) {
                const filteredSub = item.subMenu.filter(sub => hasRole(sub.roles));
                if (filteredSub.length === 0) return null;
                return { ...item, subMenu: filteredSub } as MenuItem;
            }
            return item as MenuItem;
        })
        .filter(notNull);
});

const isActive = (path: string) => {
    const current = page.url.split('?')[0];
    return current === path || current.startsWith(path + '/');
};

const isAnySubActive = (item: MenuItem) => {
    if (!item || !item.subMenu) return false;
    const current = page.url.split('?')[0];
    return item.subMenu.some((sub: MenuSubItem) => current === sub.path || current.startsWith(sub.path + '/'));
};

watch(() => page.url, () => {
    nextTick(() => {
        setTimeout(() => {
            openSubmenus.value = new Set<number>();
            filteredMenu.value.forEach((item: MenuItem, idx: number) => {
                if (item && item.subMenu && (isActive(item.path) || item.subMenu.some((sub: MenuSubItem) => isActive(sub.path)))) {
                    openSubmenus.value.add(idx);
                }
            });
        }, 100);
    });
}, { immediate: true });

// Clicking a parent item with submenu: navigate first; submenu opens after navigation via watcher to avoid flicker
function onParentClick(e: MouseEvent, index: number, item: MenuItem) {
    if (!item.subMenu || item.subMenu.length === 0) return;
    // Allow modifier/middle clicks to behave normally
    if ((e as MouseEvent).metaKey || (e as MouseEvent).ctrlKey || (e as MouseEvent).shiftKey || (e as MouseEvent).altKey || (e as MouseEvent).button !== 0) {
        return;
    }
    // Do not toggle here; let the route change occur and the watcher will open the submenu after navigation
}

defineProps({
    collapsible: {
        type: [Boolean, String],
        required: false,
    },
    variant: {
        type: String,
        required: false,
    },
});


</script>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #1e40af #0C376D;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #0C376D;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1e40af;
    border-radius: 10px;
    border: 1px solid #0C376D;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #3b82f6;
}
.transition-all {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/*LOGOTIPO*/
.app-logo{
    width:320px;
    margin-top: 6px;
}

.app-logo img {
    width: 100%;
    height: auto;
    object-fit: contain;
}

</style>

<template>
    <aside class="fixed top-0 left-0 lg:w-[320px] bg-[#2A3F54] text-white flex flex-col h-screen">
        <div class="flex items-center justify-center">
            <a href="/dashboard" class="flex items-center app-logo">
                <img src="/images/sira_logo_horizontal_white.svg" alt="logo SIRA" class="top-[-10px]"/>
            </a>
        </div>
        <nav class="flex-1 overflow-hidden flex flex-col h-full ml-2">
            <ul class="overflow-y-auto flex-grow custom-scrollbar">
                <li v-for="(item, index) in filteredMenu" :key="index" class="mb-2">
                    <div v-if="item.subMenu">
                        <div class="flex items-center">
                            <a
                                :href="item.path"
                                :target="item.target"
                                :aria-current="(isActive(item.path) || isAnySubActive(item)) ? 'page' : undefined"
                                :aria-expanded="isSubmenuOpen(index) ? 'true' : 'false'"
                                @click="(e) => onParentClick(e, index, item)"
                                :class="[
                                        'flex items-center p-3 text-md font-medium transition duration-300 rounded-l-lg flex-grow',
                                        (isActive(item.path) || isAnySubActive(item))
                                        ? 'bg-[#43CFFC] text-[#121212] font-bold shadow tracking-wide'
                                        : 'flex items-center p-3 text-md hover:text-[#121212] hover:bg-[#43CFFC] hover:shadow-lg transition duration-300 rounded-l-lg'
                                ]"
                            >
                                <component
                                    :is="item.icon"
                                    class="mr-3 h-5 w-5 flex-shrink-0"
                                />
                                <span class="truncate">{{ item.name }}</span>
                            </a>
                            <button
                                @click="toggleSubmenu(index)"
                                class="flex items-center p-3 font-medium transition duration-300 rounded-r-lg hover:bg-[#43CFFC] hover:text-[#121212] hover:shadow hover:font-bold hover:tracking-wide mr-2"
                            >
                                <ChevronDown
                                    class="h-6 w-4 transition-transform duration-500 ease-in-out"
                                    :class="{ 'rotate-180': isSubmenuOpen(index) }"
                                />
                            </button>
                        </div>
                        <div
                            class="overflow-hidden transition-all duration-500 ease-in-out transform"
                            :class="isSubmenuOpen(index) ? 'max-h-96 opacity-100 translate-y-0' : 'max-h-0 opacity-0 translate-y-2'"
                            style="will-change: transform, opacity, max-height"
                        >
                            <ul v-if="item.subMenu" class="ml-6 mt-2 space-y-1 transition-all duration-500 ease-in-out"
                                :class="{ 'scale-100': isSubmenuOpen(index), 'scale-95': !isSubmenuOpen(index) }"
                            >
                                <li v-for="(subItem, subIndex) in item.subMenu" :key="subIndex">
                                    <a
                                        :href="subItem.path"
                                        :target="subItem.target"
                                        :aria-current="isActive(subItem.path) ? 'page' : undefined"
                                        :class="[
                                                'flex items-center p-2 text-md font-medium transition duration-300 rounded-lg mr-1',
                                                isActive(subItem.path)
                                                ? 'bg-[#43CFFC] text-[#121212] font-bold shadow tracking-wide'
                                                : 'flex items-center p-3 text-md hover:text-[#121212] hover:bg-[#43CFFC] hover:shadow-lg transition duration-300 rounded-lg'
                                        ]"
                                    >
                                        <component
                                            :is="Circle"
                                            class="mr-2 h-2 w-2 text-red"
                                        />
                                        <span class="whitespace break-words">{{ subItem.name }}</span>
                                    </a>
                                </li>
                            </ul>

                        </div>
                    </div>
                    <!-- Regular menu item without submenu -->
                    <a
                        v-else
                        :href="item.path"
                        :target="item.target"
                        :aria-current="isActive(item.path) ? 'page' : undefined"
                        :class="[
                                 'flex items-center p-3 text-md font-medium transition duration-300 rounded-lg mr-2',
                                  isActive(item.path)
                                  ? 'bg-[#43CFFC] text-[#121212] font-bold shadow tracking-wide'
                                  : 'flex items-center p-3 text-md hover:text-[#121212] hover:bg-[#43CFFC] hover:shadow-lg transition duration-300 rounded-lg'
                        ]"
                    >
                        <component
                            :is="item.icon"
                            class="mr-3 h-5 w-5 flex-shrink-0"
                        />
                        <span class="truncate">{{ item.name }}</span>
                    </a>
                </li>
            </ul>
            <div class="mt-auto py-2 text-center">
                <p class="text-sm">Equipo SIRA <br> Todos los Derechos Reservados 2025</p>
            </div>
        </nav>
    </aside>
    <!-- Espaciador para compensar el sidebar fijo -->
    <div class="lg:w-[340px] flex-shrink-0"></div>

</template>
