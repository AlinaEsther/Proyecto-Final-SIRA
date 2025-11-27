<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseTable from '@/components/BaseTable.vue';
import BaseSelect from '@/components/BaseSelect.vue';
import type { BreadcrumbItem } from '@/types';
import { Columns, FileSpreadsheet, Filter, X, FileCheck, History, LayoutGrid } from 'lucide-vue-next';

interface Grade {
    id: number;
    points_earned: number;
    feedback: string | null;
    created_at: string;
    student: {
        id: number;
        person: {
            full_name: string;
        };
    };
    activity: {
        id: number;
        title: string;
        max_points: number;
        section: {
            course: {
                name: string;
                code: string;
            };
        };
    };
}

interface Student {
    id: number;
    person: {
        full_name: string;
    };
}

interface Course {
    id: number;
    code: string;
    name: string;
}

interface DetailedPerformance {
    section_id: number;
    period: string;
    code: string;
    course_name: string;
    assignments: number;
    p1: number | string;
    p2: number | string;
    p3: number | string;
    final: number | string;
    total: number | string;
    absences: number;
    allowed_absences: number;
}

interface AcademicHistoryCourse {
    code: string;
    name: string;
    credits: number;
    grade: number | string;
    letter: string;
    points: number;
}

interface AcademicHistory {
    period: string;
    career: string;
    condition: string;
    credits: number;
    gpa: number;
    courses: AcademicHistoryCourse[];
}

interface Props {
    grades: Grade[];
    detailedPerformance?: DetailedPerformance[];
    academicHistory?: AcademicHistory[];
    filters?: {
        search?: string;
        student_id?: number;
        course_id?: number;
        activity_type?: string;
        min_grade?: number;
        max_grade?: number;
        period?: string;
    };
    students?: Student[];
    courses?: Course[];
    availablePeriods?: string[];
}

const props = defineProps<Props>();

// Sistema de tabs
// Profesores empiezan en 'general', estudiantes en 'detailed'
const activeTab = ref<'general' | 'detailed' | 'history'>(
    (props.students || props.courses) ? 'general' : 'detailed'
);

// Estado del acordeón de periodos
const expandedPeriods = ref<Set<number>>(new Set([0])); // Primer periodo expandido por defecto

const togglePeriod = (index: number) => {
    if (expandedPeriods.value.has(index)) {
        expandedPeriods.value.delete(index);
    } else {
        expandedPeriods.value.add(index);
    }
};

const tabs = computed(() => {
    const allTabs = [
        { id: 'general' as const, name: 'General', icon: LayoutGrid },
        { id: 'detailed' as const, name: 'Rendimiento Detallado', icon: FileCheck },
        { id: 'history' as const, name: 'Histórico Académico', icon: History },
    ];

    // Profesores: Ven General + Detallado (solo SUS cursos con ese estudiante)
    // NO ven Histórico (eso es información privada completa del estudiante)
    if (props.students || props.courses) {
        // Es profesor o admin
        if (!props.filters?.student_id) {
            return [allTabs[0]]; // Solo General si no hay estudiante seleccionado
        }
        // Si hay estudiante seleccionado: General + Detallado (filtrado a cursos del profesor)
        return [allTabs[0], allTabs[1]];
    }

    // Estudiantes: Ven Detallado + Histórico (su propia información académica completa)
    return [allTabs[1], allTabs[2]];
});

// Mapear estudiantes para BaseSelect
const mappedStudents = computed(() =>
    props.students?.map(student => ({
        id: student.id,
        full_name: student.person?.full_name || 'Sin nombre'
    })) || []
);

// Estado de filtros
const showAdvancedFilters = ref(false);
const filterForm = ref({
    student_id: props.filters?.student_id || undefined,
    course_id: props.filters?.course_id || undefined,
    activity_type: props.filters?.activity_type || undefined,
    min_grade: props.filters?.min_grade || undefined,
    max_grade: props.filters?.max_grade || undefined,
});

const activityTypes = [
    { id: 'homework', name: 'Tarea' },
    { id: 'exam', name: 'Examen' },
    { id: 'project', name: 'Proyecto' },
    { id: 'quiz', name: 'Quiz' },
];

const applyFilters = () => {
    router.get(route('academic-performance.index'), {
        ...props.filters,
        student_id: filterForm.value.student_id,
        course_id: filterForm.value.course_id,
        activity_type: filterForm.value.activity_type,
        min_grade: filterForm.value.min_grade,
        max_grade: filterForm.value.max_grade,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        student_id: undefined,
        course_id: undefined,
        activity_type: undefined,
        min_grade: undefined,
        max_grade: undefined,
    };
    router.get(route('academic-performance.index'), {}, {
        replace: true,
    });
};

const hasActiveFilters = computed(() => {
    return !!(filterForm.value.student_id ||
             filterForm.value.course_id ||
             filterForm.value.activity_type ||
             filterForm.value.min_grade !== undefined ||
             filterForm.value.max_grade !== undefined);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Rendimiento Académico', href: '/academic-performance' }
];

const first = ref(0);

const columns = [
    {
        key: 'student',
        field: 'student',
        header: 'Estudiante',
        sortable: true,
        width: '25%',
        body: (row: Grade) => row.student?.person?.full_name || 'N/A'
    },
    {
        key: 'course',
        field: 'course',
        header: 'Curso',
        sortable: true,
        width: '20%',
        body: (row: Grade) => {
            const course = row.activity?.section?.course;
            return course ? `${course.code} - ${course.name}` : 'N/A';
        }
    },
    {
        key: 'activity',
        field: 'activity',
        header: 'Actividad',
        sortable: true,
        width: '20%',
        body: (row: Grade) => row.activity?.title || 'N/A'
    },
    {
        key: 'points_earned',
        field: 'points_earned',
        header: 'Calificación',
        sortable: true,
        width: '15%',
        align: 'center' as const,
    },
    {
        key: 'created_at',
        field: 'created_at',
        header: 'Fecha',
        sortable: true,
        width: '20%',
        body: (row: Grade) => row.created_at ? new Date(row.created_at).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }) : '-'
    }
];

const headerButtons = [
    {
        id: 'columns',
        label: 'Columnas',
        icon: Columns
    },
    {
        id: 'excel',
        label: 'Excel',
        icon: FileSpreadsheet
    }
];

const onPageChange = (event: any) => {
    first.value = event.first;
};

// Columnas para rendimiento detallado
const detailedColumns = [
    {
        key: 'code',
        field: 'code',
        header: 'Código',
        sortable: true,
        width: '10%',
    },
    {
        key: 'course_name',
        field: 'course_name',
        header: 'Asignatura',
        sortable: true,
        width: '20%',
    },
    {
        key: 'assignments',
        field: 'assignments',
        header: 'Asignaciones',
        sortable: true,
        width: '10%',
        align: 'center' as const,
    },
    {
        key: 'p1',
        field: 'p1',
        header: '1er Parcial',
        sortable: true,
        width: '10%',
        align: 'center' as const,
    },
    {
        key: 'p2',
        field: 'p2',
        header: '2do Parcial',
        sortable: true,
        width: '10%',
        align: 'center' as const,
    },
    {
        key: 'p3',
        field: 'p3',
        header: '3er Parcial',
        sortable: true,
        width: '10%',
        align: 'center' as const,
    },
    {
        key: 'final',
        field: 'final',
        header: 'Final',
        sortable: true,
        width: '10%',
        align: 'center' as const,
    },
    {
        key: 'total',
        field: 'total',
        header: 'Total',
        sortable: true,
        width: '10%',
        align: 'center' as const,
    },
    {
        key: 'absences',
        field: 'absences',
        header: 'Ausencias',
        sortable: false,
        width: '10%',
        align: 'center' as const,
        body: (row: DetailedPerformance) => `${row.absences}/${row.allowed_absences}`
    },
];

// Filtros de periodo únicos
const availablePeriods = computed(() => {
    if (!props.availablePeriods || props.availablePeriods.length === 0) return [];
    return props.availablePeriods.map(p => ({ id: p, name: p }));
});

const selectedPeriodFilter = ref(props.filters?.period || undefined);

const applyPeriodFilter = () => {
    router.get(route('academic-performance.index'), {
        ...props.filters,
        period: selectedPeriodFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <div class="p-6 border-b bg-white rounded-[15px]">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            Rendimiento Académico
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Visualiza el rendimiento y calificaciones de los estudiantes
                        </p>
                    </div>
                    <!-- Botón de filtros avanzados (solo si no es estudiante) -->
                    <button
                        v-if="students || courses"
                        @click="showAdvancedFilters = !showAdvancedFilters"
                        class="flex items-center gap-2 rounded-full px-4 py-2 font-semibold text-white transition-all duration-300 focus:scale-[1]"
                        :class="hasActiveFilters ? 'bg-blue-600 hover:bg-blue-700 hover:scale-[1.1]' : 'bg-gray-500 hover:bg-gray-600 hover:scale-[1.05]'"
                    >
                        <Filter class="h-5 w-5" />
                        <span>{{ showAdvancedFilters ? 'Ocultar Filtros' : 'Filtros Avanzados' }}</span>
                        <span v-if="hasActiveFilters" class="ml-1 px-2 py-0.5 bg-white text-blue-600 rounded-full text-xs font-bold">
                            {{ [filterForm.student_id, filterForm.course_id, filterForm.activity_type, filterForm.min_grade, filterForm.max_grade].filter(f => f !== undefined).length }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Sistema de Tabs -->
            <div v-if="tabs.length > 1" class="bg-white rounded-[15px] p-2">
                <div class="flex gap-2 border-b border-gray-200">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-2 px-4 py-3 font-medium text-sm rounded-t-lg transition-all duration-200"
                        :class="activeTab === tab.id
                            ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50'
                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                    >
                        <component :is="tab.icon" class="h-5 w-5" />
                        <span>{{ tab.name }}</span>
                    </button>
                </div>
            </div>

            <!-- Panel de filtros avanzados -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-4 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-4 opacity-0"
            >
                <div v-if="showAdvancedFilters && (students || courses)" class="p-6 bg-white rounded-[15px]">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros Avanzados</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        <!-- Estudiante -->
                        <div v-if="students">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Estudiante
                            </label>
                            <BaseSelect
                                v-model="filterForm.student_id"
                                :options="mappedStudents"
                                optionLabel="full_name"
                                optionValue="id"
                                placeholder="Todos los estudiantes"
                                entity="estudiantes"
                                :clearable="true"
                            />
                        </div>

                        <!-- Curso -->
                        <div v-if="courses">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Curso
                            </label>
                            <BaseSelect
                                v-model="filterForm.course_id"
                                :options="courses"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Todos los cursos"
                                entity="cursos"
                                :clearable="true"
                            >
                                <template #option="{ option }">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-semibold text-blue-600 text-sm">{{ option.code }}</span>
                                        <span class="text-sm">{{ option.name }}</span>
                                    </div>
                                </template>
                            </BaseSelect>
                        </div>

                        <!-- Tipo de Actividad -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Actividad
                            </label>
                            <BaseSelect
                                v-model="filterForm.activity_type"
                                :options="activityTypes"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Todos los tipos"
                                entity="tipos"
                                :clearable="true"
                                :filter="false"
                            />
                        </div>

                        <!-- Rango de Calificaciones -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Rango de Calificaciones
                            </label>
                            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                                <div class="flex items-center gap-4">
                                    <!-- Input Mínimo -->
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-600 mb-1">Mínimo</label>
                                        <input
                                            v-model.number="filterForm.min_grade"
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.1"
                                            class="text-gray-900 text-sm rounded-lg block w-full p-2 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="0"
                                        />
                                    </div>

                                    <!-- Separador visual -->
                                    <div class="flex-shrink-0 pt-5">
                                        <div class="h-px w-8 bg-gray-400"></div>
                                    </div>

                                    <!-- Input Máximo -->
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-600 mb-1">Máximo</label>
                                        <input
                                            v-model.number="filterForm.max_grade"
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.1"
                                            class="text-gray-900 text-sm rounded-lg block w-full p-2 border border-gray-300 hover:border-gray-400 focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="100"
                                        />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Filtra calificaciones entre {{ filterForm.min_grade || 0 }} y {{ filterForm.max_grade || 100 }} puntos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex items-center justify-end gap-3">
                        <button
                            @click="clearFilters"
                            :disabled="!hasActiveFilters"
                            class="flex items-center gap-2 rounded-full px-4 py-2 font-semibold text-white transition-transform duration-200 hover:scale-[1.05] focus:scale-[1] bg-gray-500/75 hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                        >
                            <X class="h-5 w-5"/>
                            <span>Limpiar</span>
                        </button>
                        <button
                            @click="applyFilters"
                            class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-transform duration-200 hover:bg-blue-700 hover:scale-[1.1] focus:scale-[1]"
                        >
                            <Filter class="h-5 w-5"/>
                            <span>Aplicar Filtros</span>
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- TAB: General - Vista de todas las calificaciones -->
            <div v-show="activeTab === 'general'">
                <BaseTable
                    :value="grades"
                    :columns="columns"
                    :headerButtons="headerButtons"
                    :loading="false"
                    :paginator="true"
                    :rows="15"
                    :rows-per-page-options="[10, 15, 25, 50]"
                    :totalVisible="5"
                    :first="first"
                    data-key="id"
                    emptyMessage="No hay calificaciones registradas todavía"
                    @page-change="onPageChange"
                    class="bg-white rounded-[15px] p-6"
                >
                    <template #body-points_earned="{ data }">
                        <div class="flex justify-center">
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800': (data.points_earned / (data.activity?.max_points || 100)) * 100 >= 90,
                                    'bg-blue-100 text-blue-800': (data.points_earned / (data.activity?.max_points || 100)) * 100 >= 80 && (data.points_earned / (data.activity?.max_points || 100)) * 100 < 90,
                                    'bg-yellow-100 text-yellow-800': (data.points_earned / (data.activity?.max_points || 100)) * 100 >= 70 && (data.points_earned / (data.activity?.max_points || 100)) * 100 < 80,
                                    'bg-red-100 text-red-800': (data.points_earned / (data.activity?.max_points || 100)) * 100 < 70
                                }"
                            >
                                {{ Number(data.points_earned).toFixed(1) }}/{{ data.activity?.max_points || 100 }}
                            </span>
                        </div>
                    </template>
                </BaseTable>
            </div>

            <!-- TAB: Detallado - Rendimiento por curso y periodo -->
            <div v-show="activeTab === 'detailed'">
                <!-- Filtro de periodo -->
                <div v-if="availablePeriods.length > 0" class="p-4 bg-white rounded-[15px] mb-4">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-gray-700">Filtrar por periodo:</label>
                        <BaseSelect
                            v-model="selectedPeriodFilter"
                            :options="availablePeriods"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos los periodos"
                            entity="periodos"
                            :clearable="true"
                            :filter="false"
                            class="w-64"
                        />
                        <button
                            @click="applyPeriodFilter"
                            class="flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 font-semibold text-white transition-transform duration-200 hover:bg-blue-700 hover:scale-[1.05] focus:scale-[1]"
                        >
                            <Filter class="h-4 w-4"/>
                            <span>Aplicar</span>
                        </button>
                    </div>
                </div>

                <BaseTable
                    v-if="detailedPerformance && detailedPerformance.length > 0"
                    :value="detailedPerformance"
                    :columns="detailedColumns"
                    :headerButtons="[]"
                    :loading="false"
                    :paginator="false"
                    data-key="section_id"
                    :totalVisible="9"
                    emptyMessage="No hay datos de rendimiento detallado disponibles"
                    class="bg-white rounded-[15px] p-6"
                >
                    <template #body-p1="{ data }">
                        <div class="flex justify-center">
                            <span class="font-semibold" :class="{
                                'text-green-600': typeof data.p1 === 'number' && data.p1 >= 90,
                                'text-blue-600': typeof data.p1 === 'number' && data.p1 >= 80 && data.p1 < 90,
                                'text-yellow-600': typeof data.p1 === 'number' && data.p1 >= 70 && data.p1 < 80,
                                'text-red-600': typeof data.p1 === 'number' && data.p1 < 70,
                                'text-gray-400': data.p1 === '--'
                            }">
                                {{ data.p1 }}
                            </span>
                        </div>
                    </template>
                    <template #body-p2="{ data }">
                        <div class="flex justify-center">
                            <span class="font-semibold" :class="{
                                'text-green-600': typeof data.p2 === 'number' && data.p2 >= 90,
                                'text-blue-600': typeof data.p2 === 'number' && data.p2 >= 80 && data.p2 < 90,
                                'text-yellow-600': typeof data.p2 === 'number' && data.p2 >= 70 && data.p2 < 80,
                                'text-red-600': typeof data.p2 === 'number' && data.p2 < 70,
                                'text-gray-400': data.p2 === '--'
                            }">
                                {{ data.p2 }}
                            </span>
                        </div>
                    </template>
                    <template #body-p3="{ data }">
                        <div class="flex justify-center">
                            <span class="font-semibold" :class="{
                                'text-green-600': typeof data.p3 === 'number' && data.p3 >= 90,
                                'text-blue-600': typeof data.p3 === 'number' && data.p3 >= 80 && data.p3 < 90,
                                'text-yellow-600': typeof data.p3 === 'number' && data.p3 >= 70 && data.p3 < 80,
                                'text-red-600': typeof data.p3 === 'number' && data.p3 < 70,
                                'text-gray-400': data.p3 === '--'
                            }">
                                {{ data.p3 }}
                            </span>
                        </div>
                    </template>
                    <template #body-final="{ data }">
                        <div class="flex justify-center">
                            <span class="font-semibold" :class="{
                                'text-green-600': typeof data.final === 'number' && data.final >= 90,
                                'text-blue-600': typeof data.final === 'number' && data.final >= 80 && data.final < 90,
                                'text-yellow-600': typeof data.final === 'number' && data.final >= 70 && data.final < 80,
                                'text-red-600': typeof data.final === 'number' && data.final < 70,
                                'text-gray-400': data.final === '--'
                            }">
                                {{ data.final }}
                            </span>
                        </div>
                    </template>
                    <template #body-total="{ data }">
                        <div class="flex justify-center">
                            <span class="font-bold text-lg" :class="{
                                'text-green-600': typeof data.total === 'number' && data.total >= 90,
                                'text-blue-600': typeof data.total === 'number' && data.total >= 80 && data.total < 90,
                                'text-yellow-600': typeof data.total === 'number' && data.total >= 70 && data.total < 80,
                                'text-red-600': typeof data.total === 'number' && data.total < 70,
                                'text-gray-400': data.total === '--'
                            }">
                                {{ data.total }}
                            </span>
                        </div>
                    </template>
                </BaseTable>
                <div v-else class="bg-white rounded-[15px] p-12 text-center">
                    <FileCheck class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-600 text-lg">No hay datos de rendimiento detallado disponibles</p>
                    <p class="text-gray-500 text-sm mt-2">Selecciona un estudiante para ver su rendimiento por curso</p>
                </div>
            </div>

            <!-- TAB: Histórico Académico - Agrupado por periodos -->
            <div v-show="activeTab === 'history'">
                <div v-if="academicHistory && academicHistory.length > 0" class="space-y-4">
                    <!-- Cada periodo es un acordeón -->
                    <div
                        v-for="(periodData, index) in academicHistory"
                        :key="index"
                        class="bg-white rounded-[15px] overflow-hidden border border-gray-200 shadow-sm"
                    >
                        <!-- Header del periodo (clickeable) -->
                        <button
                            @click="togglePeriod(index)"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 hover:from-blue-700 hover:to-blue-800 transition-all"
                        >
                            <div class="flex justify-between items-start">
                                <div class="flex items-start gap-3">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 transition-transform duration-200"
                                        :class="{ 'rotate-90': expandedPeriods.has(index) }"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <div>
                                        <h3 class="text-xl font-bold mb-1 text-left">{{ periodData.period }}</h3>
                                        <p class="text-blue-100 text-sm text-left">{{ periodData.career }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex gap-4 items-center">
                                    <div>
                                        <div class="text-sm text-blue-100">Créditos</div>
                                        <div class="text-lg font-bold">{{ periodData.credits }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-blue-100">Índice</div>
                                        <div class="text-2xl font-bold">{{ periodData.gpa.toFixed(2) }}</div>
                                    </div>
                                    <div class="px-3 py-1 rounded-full text-xs font-semibold"
                                         :class="{
                                             'bg-green-100 text-green-800': periodData.condition === 'Normal',
                                             'bg-yellow-100 text-yellow-800': periodData.condition === 'Advertencia',
                                             'bg-red-100 text-red-800': periodData.condition === 'Probatoria'
                                         }">
                                        {{ periodData.condition }}
                                    </div>
                                </div>
                            </div>
                        </button>

                        <!-- Tabla de cursos del periodo (colapsable) -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="transform opacity-0 -translate-y-2"
                            enter-to-class="transform opacity-100 translate-y-0"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="transform opacity-100 translate-y-0"
                            leave-to-class="transform opacity-0 -translate-y-2"
                        >
                            <div v-show="expandedPeriods.has(index)" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción Curso</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Créditos</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nota</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Literal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(course, cidx) in periodData.courses" :key="cidx" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                                {{ course.code }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ course.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ course.credits }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="font-semibold text-lg" :class="{
                                                    'text-green-600': typeof course.grade === 'number' && course.grade >= 90,
                                                    'text-blue-600': typeof course.grade === 'number' && course.grade >= 80 && course.grade < 90,
                                                    'text-yellow-600': typeof course.grade === 'number' && course.grade >= 70 && course.grade < 80,
                                                    'text-red-600': typeof course.grade === 'number' && course.grade < 70,
                                                    'text-gray-400': course.grade === '--'
                                                }">
                                                    {{ course.grade }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="font-bold text-lg" :class="{
                                                    'text-green-600': course.letter === 'A' || course.letter === 'A-',
                                                    'text-blue-600': course.letter === 'B+' || course.letter === 'B' || course.letter === 'B-',
                                                    'text-yellow-600': course.letter === 'C+' || course.letter === 'C' || course.letter === 'C-',
                                                    'text-red-600': course.letter === 'D' || course.letter === 'F',
                                                    'text-gray-400': course.letter === '--'
                                                }">
                                                    {{ course.letter }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </Transition>
                    </div>
                </div>
                <div v-else class="bg-white rounded-[15px] p-12 text-center">
                    <History class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-600 text-lg">No hay histórico académico disponible</p>
                    <p class="text-gray-500 text-sm mt-2">Selecciona un estudiante para ver su histórico académico</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
