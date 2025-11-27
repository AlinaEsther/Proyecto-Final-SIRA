<!-- resources/js/components/BaseSelect.vue -->
<script setup lang="ts">
import Select from 'primevue/select'
import {computed} from 'vue'

const props = defineProps({
    modelValue: [String, Number, Object],
    options: {type: Array, default: () => []},
    optionLabel: {type: String, default: 'name'},
    optionValue: {type: String, default: 'id'},
    placeholder: {type: String, default: 'Seleccione una opción'},
    disabled: {type: Boolean, default: false},
    filter: {type: Boolean, default: true},
    filterPlaceholder: {type: String, default: 'Buscar...'},
    emptyFilterMessage: {type: String, default: 'No hay coincidencias'},
    emptyMessage: {type: String, default: ''},
    entity: {type: String, default: ''},          // Para construir mensajes dinámicos
    clearable: {type: Boolean, default: false},
    id: {type: String, default: null},
    itemSize: {type: Number, default: 45},         // Altura de cada item en px (ajustable según contenido)
    virtualScrollerMinItems: {type: Number, default: 5},     // Número mínimo de opciones para activar el virtual scroller (por defecto 5)
    pt: {type: Object, default: () => ({})}        // Permite extender estilos
})

const emit = defineEmits(['update:modelValue', 'change', 'blur'])

const internalValue = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v)
})

const resolvedEmptyMessage = computed(() =>
    props.emptyMessage ||
    (props.entity ? `No hay ${props.entity} registradas` : 'No hay registros')
)

// Calcular altura dinámica del virtual scroller basándose en cantidad de opciones
const virtualScrollerOptions = computed(() => {
    const itemCount = props.options.length

    // Si hay pocas opciones, no usar virtual scroller
    if (itemCount <= props.virtualScrollerMinItems) {
        return undefined
    }

    // Máximo de items visibles (5 items)
    const maxVisibleItems = 5
    const visibleItems = Math.min(itemCount, maxVisibleItems)

    // Calcular altura total del contenedor
    const scrollHeight = `${visibleItems * props.itemSize}px`

    return {
        itemSize: props.itemSize,
        scrollHeight: scrollHeight
    }
})

const mergedPt = computed(() => ({
    root: 'text-gray-900 text-sm rounded-xl block w-full p-2.5 pr-14 flex items-center justify-between relative border ' +
        (props.disabled
            ? 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-60'
            : 'border-gray-300 hover:border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'),
    overlay: 'bg-white border border-gray-300 rounded-lg shadow-lg',

    // Posicionar los iconos al extremo derecho sin solaparse
    dropdownIcon: 'absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none',
    clearIcon: 'absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600',

    // Header con filtro
    header: 'p-3 border-b border-gray-200 bg-white',

    // Ajuste correcto del filtro con icono dentro del input (derecha)
    pcFilterContainer: {
        root: 'relative w-full'
    },
    pcFilter: {
        root: 'w-full pr-10 pl-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 text-sm'
    },
    pcFilterIconContainer: {
        root: 'pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400'
    },

    // Lista de opciones - el virtual scroller maneja automáticamente el scroll
    listContainer: '',
    list: virtualScrollerOptions.value ? '' : 'max-h-[225px] overflow-y-auto',
    option: 'px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0 flex items-center',

    // Mensajes vacíos
    emptyMessage: 'px-4 py-8 text-sm text-gray-500 italic text-center',
    emptyFilterMessage: 'px-4 py-8 text-sm text-gray-500 italic text-center',

    ...props.pt
}))
</script>

<template>
    <Select
        v-model="internalValue"
        :options="options"
        :optionLabel="optionLabel"
        :optionValue="optionValue"
        :placeholder="placeholder"
        :filter="filter"
        :filterPlaceholder="filterPlaceholder"
        :emptyFilterMessage="emptyFilterMessage"
        :emptyMessage="resolvedEmptyMessage"
        :disabled="disabled"
        :id="id"
        :showClear="clearable"
        :virtualScrollerOptions="virtualScrollerOptions"
        appendTo="body"
        unstyled
        :pt="mergedPt"
        @change="emit('change', $event?.value)"
        @blur="emit('blur', $event)"
    >
        <!-- Icono del filtro: el contenedor pcFilterIconContainer lo posiciona dentro del input -->
        <template #filtericon>
            <i class="pi pi-search" />
        </template>

        <template #option="slotProps">
            <slot name="option" v-bind="slotProps">
                <span>{{ slotProps.option[optionLabel] }}</span>
            </slot>
        </template>
    </Select>
</template>

