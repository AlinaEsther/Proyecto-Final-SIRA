<script setup lang="ts">
import {computed, ref, watch, onMounted} from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Popover from 'primevue/popover';
import Checkbox from 'primevue/checkbox';
import {ChevronDown, ChevronRight} from "lucide-vue-next";

interface Column {
    key?: string; // Unique key for column toggler and persistence
    field?: string; // Optional because some columns render via body
    header: string;
    sortable?: boolean;
    filter?: boolean;
    width?: string | number; // Ancho: '200px', '20%', '50vh', 200 (px), 'auto', 'min-content', 'max-content'
    minWidth?: string | number; // Ancho mínimo: mismas unidades que width
    maxWidth?: string | number; // Ancho máximo: mismas unidades que width
    align?: 'left' | 'center' | 'right';
    listable?: boolean; // Mostrar como lista
    truncate?: number; // Truncar texto (longitud máxima)
    emptyMessage?: string; // Mensaje cuando el array esté vacío
    body?: (row: any, index?: number) => any; // Render function support
    style?: string; // Inline style for cell
}

interface Action {
    name: string;
    icon?: string;
    tooltip?: string;
    classes?: string;
}

// Minimal HeaderButton shape for typing
interface HeaderButton {
    id?: string | number;
    label: string;
    icon?: any;
}

const props = defineProps<{
    value: any[];
    columns: Column[];
    actions?: Action[];
    actionsType?: 'default' | 'dynamic';
    loading?: boolean;
    paginator?: boolean;
    rows?: number;
    rowsPerPageOptions?: number[];
    headerButtons?: HeaderButton[];
    first?: number;
    dataKey?: string;
    emptyMessage?: string;
    totalVisible?: number;
    storageKey?: string;
    // NUEVO: soporte paginación server-side
    totalRecords?: number;
    lazy?: boolean;
}>();

const emits = defineEmits<{
    (e: 'update:filters', filters: any): void;
    (e: 'selection-change', selection: any): void;
    (e: 'rows-changed', rows: number): void;
    (e: 'page-change', data: any): void;
    (e: string, data: any): void;
}>();

const filters = ref<{ global: string }>({global: ''});
const selection = ref<any>(null);
const expandedRows = ref({});

// Función para truncar texto
const truncateText = (text: string, maxLength: number = 50): string => {
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

// Función helper para procesar valores de width/minWidth/maxWidth
const processWidthValue = (value: string | number | undefined): string => {
    if (value === undefined || value === null) return 'auto';

    // Si es un número, asumimos que son píxeles
    if (typeof value === 'number') {
        return `${value}px`;
    }

    // Si es string, verificamos si ya tiene unidades
    const stringValue = value.toString().trim();

    // Si está vacío, retornamos auto
    if (!stringValue) return 'auto';

    // Si ya tiene unidades CSS válidas, lo retornamos tal como está
    const hasUnits = /^(auto|inherit|initial|unset|min-content|max-content|fit-content)$/.test(stringValue) ||
        /^\d+(\.\d+)?(px|em|rem|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|fr)$/.test(stringValue);

    if (hasUnits) {
        return stringValue;
    }

    // Si es solo un número como string, agregamos px
    if (/^\d+(\.\d+)?$/.test(stringValue)) {
        return `${stringValue}px`;
    }

    // Si no coincide con ningún patrón válido, retornamos auto
    return 'auto';
};

// Función para crear el objeto de estilo de columna
const getColumnStyle = (col: Column) => {
    const style: Record<string, string> = {};

    // Procesar width
    if (col.width !== undefined) {
        style.width = processWidthValue(col.width);
    }

    // Procesar minWidth
    if (col.minWidth !== undefined) {
        style.minWidth = processWidthValue(col.minWidth);
    }

    // Procesar maxWidth
    if (col.maxWidth !== undefined) {
        style.maxWidth = processWidthValue(col.maxWidth);
    }

    // Agregar alineación si está especificada
    if (col.align) {
        style.textAlign = col.align;
    }

    // Si no hay width especificado, usar auto como fallback
    if (!style.width && !col.width) {
        style.width = 'auto';
    }

    return style;
};

watch(filters, f => emits('update:filters', f));
watch(selection, s => emits('selection-change', s));

// Genera acciones por defecto con toda la información completa
const getDefaultActions = (): Action[] => {
    return [
        {
            name: 'view',
            icon: 'pi pi-eye',
            classes: 'text-blue-600 hover:text-blue-800 hover:bg-blue-50',
            tooltip: 'Ver Detalles'
        },
        {
            name: 'edit',
            icon: 'pi pi-pencil',
            classes: 'text-green-600 hover:text-green-800 hover:bg-green-50',
            tooltip: 'Editar'
        },
        {
            name: 'delete',
            icon: 'pi pi-trash',
            classes: 'text-red-600 hover:text-red-800 hover:bg-red-50',
            tooltip: 'Eliminar'
        }
    ];
};

// Usa acciones pasadas o genera las por defecto
const computedActions = computed(() => {
    if (props.actionsType === 'default') {
        return getDefaultActions();
    }
    return props.actions || [];
});

const currentRows = ref(props.rows ?? 10);
watch(currentRows, (value) => {
    emits('rows-changed', value);
});

const rowsPerPageOptions = computed(() => props.rowsPerPageOptions ?? [5, 10, 20, 50]);

// Handle expandable rows and column visibility
const totalVisible = computed(() => {
    // Si el padre pasa 0, null o undefined, interpretamos que quiere mostrar TODAS las columnas
    // (antes 0 caía al fallback de 5 por el operador || produciendo columnas ocultas fantasma)
    if (props.totalVisible === 0 || props.totalVisible === null || props.totalVisible === undefined) {
        return props.columns.length;
    }
    // Si pasa un número mayor o igual al total de columnas, también mostramos todas
    if (typeof props.totalVisible === 'number' && props.totalVisible >= props.columns.length) {
        return props.columns.length;
    }
    return props.totalVisible as number; // ya validado arriba
});

// Popover ref for column toggler
const columnPanel = ref();

// Generate a stable key for each column (prefer explicit `key`, then `field`, then header+index)
const getColKey = (col: Column, index: number) => col?.key ?? col?.field ?? (col?.header ? `${index}:${col.header}` : `col-${index}`);
// Resolve key from a column instance by looking up its index in the current columns prop
const getColKeyByColumn = (col: Column) => {
    const idx = (props.columns as any[]).indexOf(col as any);
    return getColKey(col, idx >= 0 ? idx : 0);
};

// Column toggler state: keys of visible columns en el orden de aparición
// Ajuste: si totalVisible >= total columnas, incluimos todas explícitamente
const visibleColumnKeys = ref<string[]>(
    props.columns
        .slice(0, totalVisible.value >= props.columns.length ? props.columns.length : totalVisible.value)
        .map((c, i) => getColKey(c, i))
);

// Normalizamos duplicados accidentales (por cambios de definición dinámica)
visibleColumnKeys.value = Array.from(new Set(visibleColumnKeys.value));

// Derive visible and hidden columns preserving original order
const visibleColumns = computed(() =>
    props.columns.filter((c, i) => visibleColumnKeys.value.includes(getColKey(c, i)))
);
const hiddenColumns = computed(() =>
    props.columns.filter((c, i) => !visibleColumnKeys.value.includes(getColKey(c, i)))
);

// Toggle one column, keep order and avoid leaving zero columns visible
const toggleOneColumn = (key: string, checked: boolean) => {
    const set = new Set(visibleColumnKeys.value);
    if (checked) {
        set.add(key);
    } else {
        // prevent hiding the last remaining column
        if (set.size <= 1 && set.has(key)) return;
        set.delete(key);
    }
    visibleColumnKeys.value = props.columns
        .map((c, i) => getColKey(c, i))
        .filter(k => set.has(k));
};

// Keep visibility keys in sync if columns prop changes
watch(() => props.columns.map((c, i) => getColKey(c, i)), (newKeys) => {
    const allKeys = newKeys;
    if (totalVisible.value >= allKeys.length) {
        visibleColumnKeys.value = allKeys; // todas visibles, evita expansión innecesaria
        return;
    }
    const allowed = new Set(allKeys);
    const current = visibleColumnKeys.value.filter(k => allowed.has(k));
    if (current.length === 0) {
        visibleColumnKeys.value = allKeys.slice(0, totalVisible.value);
    } else {
        visibleColumnKeys.value = allKeys.filter(k => current.includes(k));
    }
}, { deep: false });

// Persistencia: si el totalVisible actual equivale a todas las columnas, ignoramos configuración previa almacenada
onMounted(() => {
    if (props.storageKey) {
        try {
            if (totalVisible.value >= props.columns.length) {
                // Fuerza todas visibles y no lee almacenamiento para evitar estado viejo causando expansión
                visibleColumnKeys.value = props.columns.map((c, i) => getColKey(c, i));
                return;
            }
            const raw = localStorage.getItem(props.storageKey);
            if (raw) {
                const saved = JSON.parse(raw);
                if (Array.isArray(saved)) {
                    const allowed = new Set(props.columns.map((c, i) => getColKey(c, i)));
                    const valid = saved.filter((k: string) => allowed.has(k));
                    if (valid.length > 0) {
                        visibleColumnKeys.value = props.columns
                            .map((c, i) => getColKey(c, i))
                            .filter(k => valid.includes(k));
                    }
                }
            }
        } catch { /* silencioso */ }
    }
});

watch(visibleColumnKeys, (keys) => {
    if (props.storageKey) {
        localStorage.setItem(props.storageKey, JSON.stringify(keys));
    }
}, { deep: true });

const updateRows = () => {
    emits('rows-changed', currentRows.value);
};

const expandAll = () => {
    if (props.value && props.value.length > 0) {
        const allExpanded: Record<string, boolean> = {};
        props.value.forEach(item => {
            if (props.dataKey && item[props.dataKey]) {
                allExpanded[item[props.dataKey]] = true;
            }
        });
        expandedRows.value = allExpanded;
    }
};

const collapseAll = () => {
    expandedRows.value = {};
};

const exportToExcel = async () => {
    try {
        // Dynamic import - solo se descarga cuando el usuario exporta (ahorro: ~500 KB)
        const ExcelJS = await import('exceljs');

        const cols = visibleColumns.value as any[];
        if (!cols || cols.length === 0) return;

        const headers = cols.map((c: any) => (c.header || c.field || '').toString().toUpperCase());
        const fields = cols.map((c: any) => c.field);

        // Build data rows extracting text from listable and custom body
        const dataRows = (props.value || []).map((row: any, rowIndex: number) => {
            return fields.map((field, i) => {
                const col: any = cols[i];
                let value: any;
                if (col?.listable && Array.isArray(row[field])) {
                    value = row[field].map((item: any) => typeof item === 'string' ? item : (item?.name ?? item)).join(', ');
                } else if (typeof col?.body === 'function') {
                    const v = col.body(row, rowIndex);
                    value = typeof v === 'string' || typeof v === 'number' ? v : (v?.toString?.() ?? '');
                } else {
                    value = row[field];
                }
                return value ?? '';
            });
        });

        // Create workbook and worksheet
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Datos');

        // Title and subtitle
        const title = 'Sistema Inteligente de Recomendaciones Académicas';
        const now = new Date();
        const pad = (n: number) => String(n).padStart(2, '0');
        const timestamp = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
        const subtitle = `Exportado: ${timestamp}`;

        // Add title row (row 1)
        const titleRow = worksheet.addRow([title]);
        titleRow.font = { size: 16, bold: true };
        titleRow.height = 28;
        worksheet.mergeCells(1, 1, 1, cols.length); // Merge across all columns

        // Add subtitle row (row 2)
        const subtitleRow = worksheet.addRow([subtitle]);
        subtitleRow.font = { size: 11, italic: true };
        subtitleRow.height = 18;
        worksheet.mergeCells(2, 1, 2, cols.length);

        // Add header row (row 3)
        const headerRow = worksheet.addRow(headers);
        headerRow.font = { bold: true };
        headerRow.height = 22;
        headerRow.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFE0E0E0' }
        };

        // Add data rows
        dataRows.forEach(row => {
            worksheet.addRow(row);
        });

        // Column widths
        const pxToWch = (px: number) => Math.max(8, Math.min(60, Math.round(px / 7)));
        const clamp = (n: number, min: number, max: number) => Math.max(min, Math.min(max, n));
        const estimateTextWch = (text: any) => {
            const s = (text == null ? '' : String(text));
            return clamp(s.length + 2, 8, 60);
        };

        const sampleCount = Math.min(50, dataRows.length);
        worksheet.columns = fields.map((field, i) => {
            const col: any = cols[i];
            let fromPixels: number | null = null;
            const w = (col?.width || '').toString();
            const mw = (col?.maxWidth || '').toString();
            const pxMatch = /([0-9]+)px/.exec(w) || /([0-9]+)px/.exec(mw);
            if (pxMatch) {
                fromPixels = pxToWch(parseInt(pxMatch[1], 10));
            }

            let est = estimateTextWch(headers[i]);
            for (let r = 0; r < sampleCount; r++) {
                const cell = dataRows[r]?.[i];
                est = Math.max(est, estimateTextWch(cell));
            }
            const width = clamp(fromPixels != null ? fromPixels : est, 10, 60);
            return { width };
        });

        // AutoFilter on header row (row 3)
        worksheet.autoFilter = {
            from: { row: 3, column: 1 },
            to: { row: 3 + dataRows.length, column: cols.length }
        };

        // Generate file
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });

        // Download file
        const fileTs = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}_${pad(now.getHours())}:${pad(now.getMinutes())}`;
        const filename = `Sistema_Inteligente_de_Recomendaciones_Academicas_${fileTs}.xlsx`;

        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    } catch (err) {
        console.error('Error exporting to Excel:', err);
    }
};

// Keep expansion state coherent with hidden columns
// - If no hidden columns, collapse all expansions.
// - When hidden columns appear, keep rows collapsed by default (no auto-expand).
watch(() => hiddenColumns.value.length, (len, prev) => {
    if (len === 0) {
        collapseAll();
    } else if (prev === 0 && len > 0) {
        // do nothing; user decides to expand
    }
});

// Mantén solo el computed
const allRowsExpanded = computed(() => {
    if (!props.value || props.value.length === 0) return false;
    return props.value.every(item =>
        props.dataKey && item[props.dataKey] && expandedRows.value[item[props.dataKey]]
    );
});

// Handler para clicks de botones del header
const handleHeaderButtonClick = (event: Event, button: HeaderButton) => {
    const isColumnsButton = button.label === 'Columnas' || button.id === 'columns';
    const isExcelButton = button.label === 'Excel' || button.id === 'excel';

    isColumnsButton
        ? columnPanel.value?.toggle(event)
        : isExcelButton
            ? exportToExcel()
            : emits('header-click', button);
};

</script>

<template>
    <div class="border-b bg-white rounded-[15px]">
        <!-- Botones del encabezado y filtros por pagina-->
        <div class=" py-2 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button
                    class="flex items-center gap-1 rounded-md border border-gray-300 px-3 py-1 hover:bg-gray-100"
                    v-for="buttons in props.headerButtons"
                    :key="buttons.id ?? buttons.label"
                    type="button"
                    @click="(e) => handleHeaderButtonClick(e, buttons)"
                >
                    <component :is="buttons.icon" class="h-5 w-5"/>
                    {{ buttons.label }}
                </button>



                <Popover
                    ref="columnPanel"
                    unstyled
                    :pt="{ root: 'bg-white border border-gray-300 rounded-xl shadow-xl p-0 w-80 mt-1' }"
                >
                    <div class="p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-semibold text-gray-800">Columnas</div>
                            <button
                                type="button"
                                class="text-xs text-blue-600 hover:text-blue-700"
                                @click="columnPanel?.hide()"
                            >Cerrar</button>
                        </div>

                        <div class="max-h-72 overflow-auto rounded-lg border border-gray-100 divide-y divide-gray-100">
                            <label
                                v-for="(col, i) in props.columns"
                                :key="getColKey(col, i)"
                                class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer"
                            >
                                <Checkbox
                                    unstyled
                                    :pt="{
                            root: 'relative inline-flex cursor-pointer select-none align-bottom',
                            box: ({ context }) => [
                              'flex items-center justify-center w-4 h-4 rounded border-2 transition-colors duration-200',
                              { 'border-blue-600 bg-blue-600': context.checked, 'border-gray-300 bg-white hover:border-blue-400': !context.checked }
                            ],
                            input: 'w-full h-full absolute opacity-0 cursor-pointer',
                            icon: ({ context }) => [
                              'w-3 h-3 text-white transition-all duration-200',
                              { 'opacity-100 visible': context.checked, 'opacity-0 invisible': !context.checked }
                            ]
                          }"
                                    :binary="true"
                                    :modelValue="visibleColumnKeys.includes(getColKey(col, i))"
                                    @update:modelValue="(val) => toggleOneColumn(getColKey(col, i), val as boolean)"
                                />
                                <span class="text-sm text-gray-800">{{ col.header }}</span>
                            </label>
                        </div>
                    </div>
                </Popover>

                <!-- Selector personalizado de filas por página -->
                <div class="flex items-center mx-5 gap-2">
                    <span>Mostrar</span>
                    <Select
                        v-model="currentRows"
                        :options="rowsPerPageOptions"
                        @change="updateRows"
                        :pt="{
                            root: 'text-gray-900 text-sm rounded-xl block w-20  p-2.5 flex items-center justify-between relative border border-gray-300 hover:border-gray-400 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                            overlay: 'bg-white border rounded shadow-md overflow-y-auto',
                            dropdownIcon: 'w-4 h-4 text-gray-400 hover:text-gray-500 transition-transform duration-200',
                            option: ({ context }) => [
                                'px-4 py-2.5 text-sm cursor-pointer transition-all duration-150',
                                {
                                    'bg-blue-800 text-white font-semibold hover:bg-blue-700 rounded mx-1': context?.selected,
                                    'text-gray-700 hover:bg-blue-50 hover:text-blue-700': !context?.selected
                                }
                            ]
                        }"
                    />
                    <span>registros por página</span>
                </div>
            </div>

            <!-- Filtro Global -->
            <div class="relative w-64">
                <InputText
                    v-model="filters.global"
                    placeholder="Buscar..."
                    :pt="{
                        root: {
                            class: 'w-full pl-3 pr-10 p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-200'
                        }
                    }"
                />
                <i class="pi pi-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"/>
            </div>
        </div>

        <DataTable
            :value="value"
            :filters="{ global: { value: filters.global, matchMode: 'contains' } }"
            :paginator="paginator"
            :rows="currentRows"
            :rows-per-page-options="rowsPerPageOptions"
            :loading="loading"
            :data-key="props.dataKey ?? 'id'"
            selectionMode="single"
            v-model:selection="selection"
            :first="props.first"
            @page="(e) => emits('page-change', e)"
            :resizableColumns="true"
            columnResizeMode="fit"
            :emptyMessage="props.emptyMessage || 'No hay registros disponibles'"
            v-model:expandedRows="expandedRows"
            :expandedRowIcon="'pi pi-chevron-down'"
            :collapsedRowIcon="'pi pi-chevron-right'"
            :totalRecords="props.totalRecords"
            :lazy="props.lazy"
            :pt="{
                wrapper: 'space-y-4',
                table: 'border-collapse table-fixed w-auto min-w-full',
                column: {
                    headerCell: {
                        class: ['bg-gray-200/70 border-y border-gray-200 px-3 py-3',
                               'text-left text-sm font-semibold text-gray-900',
                               'first:border-l last:border-r overflow-hidden']
                    },
                    bodyCell: {
                        class: ['border-b border-gray-100 px-3 py-3 text-sm text-gray-600',
                               'first:border-l last:border-r']
                    }
                },
                pcPaginator: {
                    paginatorContainer: 'align-center',
                    root: 'flex flex-col items-end p-2',
                    content: 'flex items-center',
                    firstIcon: 'w-5 h-5 text-gray-500 hover:text-gray-700 mx-1',
                    prevIcon: 'w-5 h-5 text-gray-500 hover:text-gray-700 mx-1',
                    nextIcon: 'w-5 h-5 text-gray-500 hover:text-gray-700 mx-1',
                    lastIcon: 'w-5 h-5 text-gray-500 hover:text-gray-700 mx-1 mr-6',
                    pages: 'flex items-center space-x-2',
                    page: ({ context }) => [
                        'w-8 h-8 flex items-center justify-center rounded-full cursor-pointer',
                        {
                            'bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow-md': context?.active,
                            'hover:bg-blue-100 hover:text-blue-700 text-gray-700': !context?.active
                        }
                    ],
                    pcRowPerPageDropdown: {
                        root: 'hidden',
                    },
                },
            }"
        >
            <!-- Columna de expansión -->
            <Column v-if="hiddenColumns.length > 0" expander
                    :style="{ width: '4%', minWidth: '4%', maxWidth: '4%' }"
                    :pt="{
                    headerCell: 'bg-blue-50 border-y border-gray-200',
                    bodyCell: 'border-b border-gray-100',
                }"
            >
                <template #header>
                    <button
                        @click="allRowsExpanded ? collapseAll() : expandAll()"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-blue-100 transition-colors cursor-pointer"
                        :title="allRowsExpanded ? 'Colapsar todas las filas' : 'Expandir todas las filas'"
                    >
                        <component
                            :is="allRowsExpanded ? ChevronDown : ChevronRight"
                            class="h-5 w-5 transition-colors"
                            :class="{
                                'text-blue-600': allRowsExpanded,
                                'text-gray-900 hover:text-blue-600': !allRowsExpanded
                            }"
                        />
                    </button>
                </template>
            </Column>

            <Column
                v-for="(col, i) in visibleColumns"
                :key="getColKeyByColumn(col)"
                :field="col.field as any"
                :header="col.header"
                :sortable="col.sortable ?? false"
                :filter="col.filter ?? false"
                :style="getColumnStyle(col)"
                :pt="{
                    root: 'relative',
                    headerContent: 'flex items-center gap-2 whitespace-nowrap',
                    sortIcon: 'pi pi-sort absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity',
                    sortBadge: 'hidden',
                    bodyCell: col.listable ? 'whitespace-pre-line break-words' : 'whitespace-normal break-words'
                }"
            >
                <template #body="slotProps">
                    <!-- Si existe un slot personalizado del padre, usarlo (prioridad: key > field) -->
                    <slot v-if="$slots[`body-${col.key || col.field}`]" :name="`body-${col.key || col.field}`" :data="slotProps.data" :index="slotProps.index"></slot>

                    <!-- Renderizado para columnas tipo lista -->
                    <div v-else-if="col.listable">
                        <ul v-if="slotProps.data[col.field] && Array.isArray(slotProps.data[col.field]) && slotProps.data[col.field].length > 0"
                            class="list-none p-0 m-0 space-y-1 max-w-xs">
                            <li
                                v-for="item in slotProps.data[col.field]"
                                :key="item.id || item"
                                class="text-sm flex items-start"
                                :title="typeof item === 'string' ? item : item.name"
                            >
                                <span class="text-gray-500 mr-2 flex-shrink-0">•</span>
                                <span class="text-gray-700">
                                    {{ col.truncate ?
                                    truncateText(typeof item === 'string' ? item : item.name, col.truncate) :
                                    (typeof item === 'string' ? item : item.name)
                                    }}
                                </span>
                            </li>
                        </ul>
                        <span v-else class="text-gray-500 italic text-sm">
                            {{ col.emptyMessage || 'Sin elementos' }}
                        </span>
                    </div>

                    <!-- Renderizado normal con truncate opcional -->
                    <div v-else-if="col.truncate">
                        <span :title="col.body ? col.body(slotProps.data, slotProps.index) : slotProps.data[col.field]">
                            {{ col.body ?
                            truncateText(col.body(slotProps.data, slotProps.index), col.truncate) :
                            truncateText(slotProps.data[col.field] || '', col.truncate)
                            }}
                        </span>
                    </div>

                    <!-- Renderizado normal -->
                    <div v-else>
                        {{ col.body ? col.body(slotProps.data, slotProps.index) : slotProps.data[col.field] }}
                    </div>
                </template>
            </Column>

            <!-- Columna de Acciones -->
            <Column
                v-if="computedActions && computedActions.length > 0"
                :header="'Acciones'"
                :sortable="false"
                :style="{ width: '8%', minWidth: '8%', maxWidth: '8%' }"
                :pt="{
                    headerCell: 'text-center bg-gray-50 border-y border-gray-200',
                    bodyCell: 'text-center border-b border-gray-100'
                }"
            >
                <template #body="{ data }">
                    <div class="flex items-center justify-center space-x-1">
                        <button
                            v-for="action in computedActions"
                            :key="action.name"
                            class="p-1.5 rounded-full transition-colors"
                            :class="action.classes"
                            @click="$emit(action.name, data)"
                            :title="action.tooltip"
                        >
                            <i
                                v-if="!action.icon || (typeof action.icon === 'string' && action.icon.startsWith('pi'))"
                                :class="action.icon || 'text-sm'"
                            />
                            <component
                                v-else
                                :is="action.icon"
                                class="text-sm"
                            />
                        </button>
                    </div>
                </template>
            </Column>

            <!-- Template para filas expandidas con columnas ocultas -->
            <template #expansion="slotProps">
                <div class="bg-gray-50 border-l-4 border-blue-500">
                    <!-- Header de la sección expandida -->
                    <div class="px-6 py-3 bg-blue-100/20 rounded-t-m border-b border-gray-200">
                        <h4 class="text-sm font-medium text-blue-900">Información adicional</h4>
                    </div>

                    <!-- Contenido en formato tabla organizada -->
                    <div class="p-6 rounded-b-m">
                        <div class="grid grid-cols-[1.1fr_1fr_1fr_1fr_1fr_1fr] gap-2 text-sm">
                            <div
                                v-for="col in hiddenColumns"
                                :key="col.field || col.header"
                                class="text-sm"
                            >
                                <div class="grid grid-cols-1 gap-1">
                                    <span class="block font-semibold text-gray-700 mb-1 p-1">{{ col.header }}:</span>
                                    <div class="block text-gray-900 px-2 py-1">
                                        <!-- Renderizado directo del valor -->
                                        <template v-if="col.body && typeof col.body === 'function'">
                                            {{ col.body(slotProps.data, slotProps.index) }}
                                        </template>
                                        <template v-else-if="col.field">
                                            {{ slotProps.data[col.field] || 'N/A' }}
                                        </template>
                                        <template v-else>
                                            N/A
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </DataTable>
    </div>
</template>

<style scoped>
@reference "tailwindcss";

/* Row Toggle Button */
:deep([data-pc-section="rowtogglebutton"]) {
    @apply inline-flex items-center justify-center
    w-8 h-8 rounded-full
    border-0 bg-transparent
    text-gray-600 hover:text-blue-600 hover:bg-blue-100
    transition-colors cursor-pointer;
}

:deep([data-pc-section="rowtogglebutton"] .pi) {
    @apply text-sm transition-transform duration-200;
}

/* Chevron Icons */
:deep(.pi-chevron-right) {
    @apply text-gray-500 hover:text-blue-600;
}

:deep(.pi-chevron-down) {
    @apply text-blue-600 rotate-0;
}

/* Row Expansion Cell */
:deep([data-pc-section="rowexpansioncell"]) {
    @apply bg-gray-100/90;
}

</style>
