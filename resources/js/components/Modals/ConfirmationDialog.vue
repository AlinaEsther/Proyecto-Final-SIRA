<script setup lang="ts">
import {Dialog, DialogContent} from '@/components/ui/dialog';
import {AlertTriangle, CheckCircle, Trash2, X} from 'lucide-vue-next';
import {computed} from 'vue';

interface Props {
    isOpen: boolean;
    type: 'success' | 'confirmation' | 'delete';
    title: string;
    message?: string;
    entityName?: string;
    confirmText?: string;
    cancelText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
});

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

const modalConfig = computed(() => {
    switch (props.type) {
        case 'success':
            return {
                icon: CheckCircle,
                iconColor: 'text-green-500',
                bgColor: 'bg-green-50',
                borderColor: 'border-green-200',
                buttonColor: 'bg-green-500 hover:bg-green-600',
                showCancel: false,
            };
        case 'delete':
            return {
                icon: Trash2,
                iconColor: 'text-red-500',
                bgColor: 'bg-red-50',
                borderColor: 'border-red-200',
                buttonColor: 'bg-red-500 hover:bg-red-600',
                showCancel: true,
            };
        case 'confirmation':
        default:
            return {
                icon: AlertTriangle,
                iconColor: 'text-yellow-500',
                bgColor: 'bg-yellow-50',
                borderColor: 'border-yellow-200',
                buttonColor: 'bg-blue-500 hover:bg-blue-600',
                showCancel: true,
            };
    }
});

const displayMessage = computed(() => {
    if (props.message) return props.message;

    switch (props.type) {
        case 'success':
            return props.entityName
                ? `${props.entityName} creada satisfactoriamente`
                : 'Operación completada satisfactoriamente';
        case 'delete':
            return props.entityName
                ? `¿Está seguro que desea eliminar "${props.entityName}"?`
                : '¿Está seguro que desea eliminar este elemento?';
        case 'confirmation':
        default:
            return props.entityName
                ? `¿Está seguro que desea continuar con "${props.entityName}"?`
                : '¿Está seguro que desea continuar?';
    }
});

// Modificación clave: Ya no emitimos 'close' aquí
function handleConfirm() {
    emit('confirm');
    // Ya no cerramos automáticamente el diálogo
}

function handleCancel() {
    emit('close');
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="handleCancel">
        <DialogContent class="sm:max-w-[500px] bg-white">
            <div class="flex flex-col items-center space-y-6 p-6">
                <!-- Icon -->
                <div
                    :class="[
                                'w-20 h-20 rounded-full flex items-center justify-center border-2',
                                modalConfig.bgColor,
                                modalConfig.borderColor
                            ]"
                >
                    <component
                        :is="modalConfig.icon"
                        :class="['w-10 h-10', modalConfig.iconColor]"
                    />
                </div>

                <!-- Content -->
                <div class="text-center space-y-3">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ title }}
                    </h2>
                    <p class="text-gray-600 text-sm leading-relaxed max-w-md">
                        {{ displayMessage }}
                    </p>
                </div>

                <!-- Actions con estilos actualizados para coincidir con FormDialog -->
                <div class="flex space-x-3 pt-4">
                    <!-- Cancel button -->
                    <button
                        v-if="modalConfig.showCancel"
                        @click="handleCancel"
                        class="flex items-center gap-2 rounded-full bg-gray-500 px-4 py-2 font-semibold text-white transition duration-300 hover:bg-gray-600 transition-transform duration-200 hover:scale-[1.05] focus:scale-[1]"
                    >
                        {{ cancelText }}
                    </button>

                    <!-- Confirm button -->
                    <button
                        @click="handleConfirm"
                        :class="[
                                    'flex items-center gap-2 rounded-full px-4 py-2 font-semibold text-white transition duration-300 transition-transform duration-200 hover:scale-[1.05] focus:scale-[1]',
                                    modalConfig.buttonColor
                                ]"
                    >
                        {{ type === 'success' ? 'Aceptar' : confirmText }}
                    </button>
                </div>

<!--                &lt;!&ndash; Close button (X) &ndash;&gt;-->
<!--                <button-->
<!--                    @click="handleCancel"-->
<!--                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"-->
<!--                >-->
<!--                    <X class="w-5 h-5"/>-->
<!--                </button>-->
            </div>
        </DialogContent>
    </Dialog>
</template>
