<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';
import { CheckCircle, AlertTriangle, XCircle, X } from 'lucide-vue-next';

const { alert } = useAlert();
const isVisible = ref(false);
const timeoutId = ref<NodeJS.Timeout | null>(null);

// Configuración de iconos y colores por tipo de alerta
const alertConfig = {
    success: {
        icon: CheckCircle,
        bgClass: 'bg-green-50',
        borderClass: 'border-green-500',
        iconClass: 'text-green-500',
        textClass: 'text-green-800',
        iconBgClass: 'bg-green-100'
    },
    warning: {
        icon: AlertTriangle,
        bgClass: 'bg-amber-50',
        borderClass: 'border-amber-500',
        iconClass: 'text-amber-500',
        textClass: 'text-amber-800',
        iconBgClass: 'bg-amber-100'
    },
    danger: {
        icon: XCircle,
        bgClass: 'bg-red-50',
        borderClass: 'border-red-500',
        iconClass: 'text-red-500',
        textClass: 'text-red-800',
        iconBgClass: 'bg-red-100'
    }
};

const showAlert = () => {
    if (!alert.value) return;

    isVisible.value = true;

    // Auto-ocultar después de 5 segundos
    if (timeoutId.value) {
        clearTimeout(timeoutId.value);
    }

    timeoutId.value = setTimeout(() => {
        hideAlert();
    }, 5000);
};

const hideAlert = () => {
    isVisible.value = false;
    if (timeoutId.value) {
        clearTimeout(timeoutId.value);
        timeoutId.value = null;
    }
};

// Mostrar alerta cuando cambia
watch(alert, (newAlert) => {
    if (newAlert) {
        showAlert();
    }
}, { immediate: true });

onMounted(() => {
    if (alert.value) {
        showAlert();
    }
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-x-full opacity-0"
        enter-to-class="transform translate-x-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-x-0 opacity-100"
        leave-to-class="transform translate-x-full opacity-0"
    >
        <div
            v-if="isVisible && alert"
            class="fixed top-6 right-6 z-50 max-w-md w-full shadow-lg rounded-lg border-l-4"
            :class="[
                alertConfig[alert.type].bgClass,
                alertConfig[alert.type].borderClass
            ]"
        >
            <div class="p-4 flex items-center gap-3">
                <!-- Icono -->
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                    :class="alertConfig[alert.type].iconBgClass"
                >
                    <component
                        :is="alertConfig[alert.type].icon"
                        class="w-5 h-5"
                        :class="alertConfig[alert.type].iconClass"
                    />
                </div>

                <!-- Mensaje -->
                <div class="flex-1 min-w-0">
                    <p
                        class="text-sm font-medium leading-5"
                        :class="alertConfig[alert.type].textClass"
                    >
                        {{ alert.message }}
                    </p>
                </div>

                <!-- Botón cerrar -->
                <button
                    @click="hideAlert"
                    class="flex-shrink-0 rounded-lg p-1.5 transition-colors inline-flex items-center justify-center"
                    :class="[
                        alert.type === 'success' && 'hover:bg-green-100 text-green-500',
                        alert.type === 'warning' && 'hover:bg-amber-100 text-amber-500',
                        alert.type === 'danger' && 'hover:bg-red-100 text-red-500'
                    ]"
                    aria-label="Cerrar alerta"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>
    </Transition>
</template>
