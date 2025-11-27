<script setup lang="ts">
import MultiSelect from 'primevue/multiselect'
import {computed, ref} from 'vue'

const props = defineProps({
    modelValue: {type: Array, default: () => []},
    options: {type: Array, default: () => []},
    optionLabel: {type: String, default: 'name'},
    optionValue: {type: String, default: 'id'},
    placeholder: {type: String, default: 'Seleccione opciones'},
    disabled: {type: Boolean, default: false},
    filter: {type: Boolean, default: true},
    filterPlaceholder: {type: String, default: 'Buscar...'},
    emptyFilterMessage: {type: String, default: 'No hay coincidencias'},
    emptyMessage: {type: String, default: ''},
    entity: {type: String, default: ''},
    maxSelectedLabels: {type: Number, default: 4},
    selectedItemsLabel: {type: String, default: '{0} elementos seleccionados'},
    display: {type: String, default: 'chip'}, // 'comma' or 'chip'
    id: {type: String, default: null},
    itemSize: {type: Number, default: 45},         // Altura de cada item en px (ajustable según contenido)
    // Número mínimo de opciones para activar el virtual scroller (por defecto 5)
    virtualScrollerMinItems: {type: Number, default: 5},
    filterFields: {type: [Array, String], default: null}, // Campos por los que filtrar (ej: ['code', 'name'])
    pt: {type: Object, default: () => ({})}
})

const emit = defineEmits(['update:modelValue', 'change', 'blur'])

const multiSelectRef = ref()
const filterInput = ref()

const internalValue = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v)
})

const resolvedEmptyMessage = computed(() =>
    props.emptyMessage ||
    (props.entity ? `No hay ${props.entity} registradas` : 'No hay registros')
)

const resolvedEmptyFilterMessage = computed(() =>
    props.emptyFilterMessage ||
    (props.entity ? `No se encontraron ${props.entity} que coincidan` : 'No hay coincidencias')
)

// Calcular altura dinámica del virtual scroller basándose en cantidad de opciones
const virtualScrollerOptions = computed(() => {
    // Verificar que options esté definido y sea un array
    if (!props.options || !Array.isArray(props.options)) {
        return undefined
    }

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

// Maneja la selección/deselección de todas las opciones
const handleSelectAll = (checked: boolean, options: any[]) => {
    if (checked) {
        internalValue.value = options.map(option => option[props.optionValue])
    } else {
        internalValue.value = []
    }
}

// Maneja el filtrado manual
const handleFilter = (value: string) => {
    // Aquí necesitarías acceder a la instancia interna del MultiSelect para aplicar el filtro
    // Como alternativa, podrías emitir un evento para que el componente padre maneje el filtro
    if (multiSelectRef.value?.$refs?.overlay) {
        // Intentar aplicar el filtro internamente si es posible
        multiSelectRef.value.filter(value)
    }
}

const mergedPt = computed(() => ({
    root: 'text-gray-900 text-sm rounded-xl block w-full p-2.5 min-h-[44px] flex items-center justify-between gap-2 relative border ' +
        (props.disabled
            ? 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-60'
            : 'border-gray-300 hover:border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'),
    label: 'text-sm',
    dropdown: props.disabled ? 'text-gray-300' : 'text-gray-500 hover:text-gray-700',
    overlay: 'bg-white border border-gray-300 rounded-lg shadow-lg',

    // Header
    header: 'flex items-center justify-start gap-3 p-3 border-b border-gray-200 bg-white',
    headerCheckbox: 'flex items-center gap-2 flex-shrink-0',

    // Ajuste correcto del filtro con icono dentro del input (derecha)
    pcFilterContainer: {
        root: 'relative flex-1 ml-2'
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
    option: 'px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors flex items-center gap-3 border-b border-gray-100 last:border-b-0',

    // Mensajes vacíos
    emptyMessage: 'px-4 py-8 text-sm text-gray-500 italic text-center',
    emptyFilterMessage: 'px-4 py-8 text-sm text-gray-500 italic text-center',

    // Checkbox del header (seleccionar todo)
    pcHeaderCheckbox: {
        root: 'relative inline-flex cursor-pointer select-none align-bottom',
        box: ({ context }) => [
            'flex items-center justify-center w-4 h-4 rounded border-2 transition-colors duration-200',
            {
                'border-blue-500 bg-blue-500': context.checked,
                'border-gray-300 bg-white hover:border-blue-400': !context.checked,
                'cursor-not-allowed opacity-60': props.disabled
            }
        ],
        input: 'w-full h-full absolute opacity-0 cursor-pointer disabled:cursor-default',
        icon: ({ context }) => [
            'w-3 h-3 text-white transition-all duration-200',
            {
                'opacity-100 visible': context.checked,
                'opacity-0 invisible': !context.checked
            }
        ]
    },

    // Checkbox de cada opción
    pcOptionCheckbox: {
        root: 'relative inline-flex cursor-pointer select-none align-bottom',
        box: ({ context }) => [
            'flex items-center justify-center w-4 h-4 rounded border-2 transition-colors duration-200',
            {
                'border-blue-500 bg-blue-500': context.checked,
                'border-gray-300 bg-white hover:border-blue-400': !context.checked,
                'cursor-not-allowed opacity-60': props.disabled
            }
        ],
        input: 'w-full h-full absolute opacity-0 cursor-pointer disabled:cursor-default',
        icon: ({ context }) => [
            'w-3 h-3 text-white transition-all duration-200',
            {
                'opacity-100 visible': context.checked,
                'opacity-0 invisible': !context.checked
            }
        ]
    },

    // Chips dentro de MultiSelect (PrimeVue Chip)
    pcChip: {
        root: 'inline-flex items-center justify-start gap-2 m-1 bg-blue-50 text-blue-800 text-sm px-3 py-1 rounded-full ring-1 ring-blue-200/70 transition-all duration-200 hover:bg-blue-100',
        label: 'text-blue-800',
    },

    ...props.pt
}))
</script>

<template>
    <MultiSelect
        v-model="internalValue"
        :options="options"
        :optionLabel="optionLabel"
        :optionValue="optionValue"
        :placeholder="placeholder"
        :filter="filter"
        :filterPlaceholder="filterPlaceholder"
        :filterFields="filterFields"
        :emptyFilterMessage="resolvedEmptyFilterMessage"
        :emptyMessage="resolvedEmptyMessage"
        :disabled="disabled"
        :id="id"
        :maxSelectedLabels="maxSelectedLabels"
        :selectedItemsLabel="selectedItemsLabel"
        :virtualScrollerOptions="virtualScrollerOptions"
        :display="display"
        appendTo="body"
        ref="multiSelectRef"
        unstyled
        :pt="mergedPt"
        @change="emit('change', $event?.value)"
        @blur="emit('blur', $event)"
    >
        <!-- Checkbox personalizado para header -->
        <template #headercheckboxicon="{ checked }">
            <svg
                v-if="checked"
                class="w-3 h-3 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                />
            </svg>
        </template>
        <!-- Icono del filtro: el contenedor pcFilterIconContainer lo posiciona dentro del input -->
        <template #filtericon>
            <i class="pi pi-search" />
        </template>
        <!-- Checkbox personalizado para opciones -->
        <template #optioncheckboxicon="{ checked }">
            <svg
                v-if="checked"
                class="w-3 h-3 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                />
            </svg>
        </template>

        <template #option="slotProps">
            <slot name="option" v-bind="slotProps">
                <span>{{ slotProps.option[optionLabel] }}</span>
            </slot>
        </template>

        <!-- Solo mostrar el slot chip si el padre lo define -->
        <template v-if="$slots.chip" #chip="slotProps">
            <slot name="chip" v-bind="slotProps" />
        </template>
    </MultiSelect>
</template>


<style scoped>
@reference "tailwindcss";
:deep([data-pc-name="pcchip"] [data-pc-section="label"]) {
    @apply cursor-pointer;
}
:deep([data-pc-name="pcchip"] [data-pc-section="removeicon"]) {
    @apply rounded-full text-blue-600 hover:text-blue-800 hover:bg-blue-200 cursor-pointer;
}
</style>
