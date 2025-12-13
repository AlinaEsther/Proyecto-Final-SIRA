<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import LineChart from '@/components/charts/LineChart.vue';
import BarChart from '@/components/charts/BarChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import { type BreadcrumbItem } from '@/types';
import { InfoIcon } from 'lucide-vue-next';

interface Props {
    role: 'Estudiante' | 'Profesor' | 'Administrador';
    stats: any;
    activeSections?: any[];
    recentGrades?: any[];
    recommendations?: any[];
    sections?: any[];
    recentSections?: any[];
    recentRecommendations?: any[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }
];

// Datos para gráficos según rol
const gradesChartData = computed(() => {
    if (props.role !== 'Estudiante' || !props.recentGrades || props.recentGrades.length === 0) return null;

    const grades = props.recentGrades.slice().reverse();
    return {
        labels: grades.map((g: any) => g.activity.substring(0, 15) + '...'),
        datasets: [
            {
                label: 'Calificación (%)',
                data: grades.map((g: any) => g.percentage),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
            },
        ],
    };
});

const sectionsChartData = computed(() => {
    if (props.role !== 'Profesor' || !props.sections || props.sections.length === 0) return null;

    return {
        labels: props.sections.map((s: any) => s.course_code),
        datasets: [
            {
                label: 'Estudiantes',
                data: props.sections.map((s: any) => s.students_count),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
            },
        ],
    };
});

const recommendationsStatusChart = computed(() => {
    if (props.role !== 'Administrador' || !props.recentRecommendations || props.recentRecommendations.length === 0) return null;

    const statuses = props.recentRecommendations.reduce((acc: any, rec: any) => {
        acc[rec.status] = (acc[rec.status] || 0) + 1;
        return acc;
    }, {});

    const statusLabels: any = {
        pending: 'Pendientes',
        viewed: 'Vistas',
        completed: 'Completadas',
        dismissed: 'Descartadas',
    };

    return {
        labels: Object.keys(statuses).map(k => statusLabels[k] || k),
        data: Object.values(statuses),
    };
});

// Datos dinámicos según el rol para las tarjetas
const card1Title = computed(() => {
    if (props.role === 'Estudiante') return 'Secciones Activas';
    if (props.role === 'Profesor') return 'Secciones Activas';
    return 'Secciones Activas';
});

const card1Value = computed(() => {
    if (!props.stats) return 0;
    if (props.role === 'Estudiante') return props.stats.active_sections || 0;
    if (props.role === 'Profesor') return props.stats.active_sections || 0;
    return props.stats.total_sections || 0;
});

const card2Title = computed(() => {
    if (props.role === 'Estudiante') return 'Promedio General';
    if (props.role === 'Profesor') return 'Total Estudiantes';
    return 'Total Estudiantes';
});

const card2Value = computed(() => {
    if (!props.stats) return '0';
    if (props.role === 'Estudiante') return (props.stats.average_grade || 0) + '%';
    if (props.role === 'Profesor') return props.stats.total_students || 0;
    return props.stats.total_students || 0;
});

const card3Title = computed(() => {
    if (props.role === 'Estudiante') return 'Recomendaciones Pendientes';
    if (props.role === 'Profesor') return 'Recomendaciones Generadas';
    return 'Recomendaciones IA';
});

const card3Value = computed(() => {
    if (!props.stats) return 0;
    if (props.role === 'Estudiante') return props.stats.pending_recommendations || 0;
    if (props.role === 'Profesor') return props.stats.recommendations_generated || 0;
    return props.stats.total_recommendations || 0;
});

const card3Subtitle = computed(() => {
    if (props.role === 'Estudiante') return 'A revisar';
    if (props.role === 'Profesor') return 'Para tus estudiantes';
    return 'Total del sistema';
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full rounded-xl">
            <!-- Sección izquierda: tarjetas y gráficas -->
            <div class="flex-1 h-full overflow-y-auto">
                <div class="p-6 flex flex-col gap-8">
                    <!-- Grid 2 columnas para Tarjetas A y B -->
                    <div class="flex flex-row justify-between gap-6">
                        <!-- Columna 1: Tarjeta A + Contenido A -->
                        <div class="flex flex-col w-1/2 gap-0.5">
                            <!-- Tarjeta A -->
                            <div class="flex-none relative shadow-md bg-white p-6 flex items-start rounded-xl h-32">
                                <div class="flex-1">
                                    <h2 class="text-xl font-semibold text-gray-700">{{ card1Title }}</h2>
                                    <div class="text-5xl font-bold text-blue-800 mt-2">{{ card1Value }}</div>
                                </div>
                                <InfoIcon class="w-7 h-7 text-gray-400"/>
                            </div>

                            <!-- Contenido A -->
                            <div class="flex-1 relative bg-white shadow-md p-6 rounded-xl mt-2 min-h-[300px]">
                                <!-- ESTUDIANTE: Lista de secciones -->
                                <template v-if="role === 'Estudiante'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Mis Secciones</h3>
                                    <div v-if="activeSections && activeSections.length > 0" class="space-y-3">
                                        <div
                                            v-for="section in activeSections"
                                            :key="section.id"
                                            class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                        >
                                            <p class="font-semibold text-gray-800 text-sm">{{ section.name }}</p>
                                            <p class="text-xs text-gray-600">{{ section.code }}</p>
                                            <p class="text-xs text-gray-500 mt-1">Prof. {{ section.professor }}</p>
                                        </div>
                                    </div>
                                    <p v-else class="text-gray-500 text-center py-8">No estás inscrito en secciones</p>
                                </template>

                                <!-- PROFESOR: Lista de secciones -->
                                <template v-if="role === 'Profesor'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Mis Secciones</h3>
                                    <div v-if="sections && sections.length > 0" class="space-y-3">
                                        <div
                                            v-for="section in sections.slice(0, 5)"
                                            :key="section.id"
                                            class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                        >
                                            <p class="font-semibold text-gray-800 text-sm">{{ section.name }}</p>
                                            <p class="text-xs text-gray-600">{{ section.course_code }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ section.students_count }} estudiantes</p>
                                        </div>
                                    </div>
                                    <p v-else class="text-gray-500 text-center py-8">No tienes secciones asignadas</p>
                                </template>

                                <!-- ADMIN: Secciones recientes -->
                                <template v-if="role === 'Administrador'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Secciones Recientes</h3>
                                    <div v-if="recentSections && recentSections.length > 0" class="space-y-3">
                                        <div
                                            v-for="section in recentSections"
                                            :key="section.id"
                                            class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                        >
                                            <p class="font-semibold text-gray-800 text-sm">{{ section.name }}</p>
                                            <p class="text-xs text-gray-600">{{ section.professor }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ section.students_count }} estudiantes</p>
                                        </div>
                                    </div>
                                    <p v-else class="text-gray-500 text-center py-8">No hay secciones recientes</p>
                                </template>
                            </div>
                        </div>

                        <!-- Columna 2: Tarjeta B + Gráfico B -->
                        <div class="flex flex-col w-1/2 gap-0.5">
                            <!-- Tarjeta B -->
                            <div class="flex-none relative shadow-md bg-white p-6 flex items-start rounded-xl h-32">
                                <div class="flex-1">
                                    <h2 class="text-xl font-semibold text-gray-700">{{ card2Title }}</h2>
                                    <div class="text-5xl font-bold text-blue-800 mt-2">{{ card2Value }}</div>
                                </div>
                                <InfoIcon class="w-7 h-7 text-gray-400"/>
                            </div>

                            <!-- Gráfico B -->
                            <div class="flex-1 relative bg-white shadow-md p-6 rounded-xl mt-2 min-h-[300px]">
                                <!-- ESTUDIANTE: Gráfico de rendimiento -->
                                <template v-if="role === 'Estudiante'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Rendimiento Reciente</h3>
                                    <LineChart
                                        v-if="gradesChartData"
                                        :labels="gradesChartData.labels"
                                        :datasets="gradesChartData.datasets"
                                        :height="220"
                                    />
                                    <p v-else class="text-gray-500 text-center py-8">No hay calificaciones registradas</p>
                                </template>

                                <!-- PROFESOR: Gráfico de estudiantes por sección -->
                                <template v-if="role === 'Profesor'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Estudiantes por Sección</h3>
                                    <BarChart
                                        v-if="sectionsChartData"
                                        :labels="sectionsChartData.labels"
                                        :datasets="sectionsChartData.datasets"
                                        :height="220"
                                    />
                                    <p v-else class="text-gray-500 text-center py-8">No hay secciones asignadas</p>
                                </template>

                                <!-- ADMIN: Gráfico de distribución de recomendaciones -->
                                <template v-if="role === 'Administrador'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Distribución de Recomendaciones</h3>
                                    <DoughnutChart
                                        v-if="recommendationsStatusChart"
                                        :labels="recommendationsStatusChart.labels"
                                        :data="recommendationsStatusChart.data"
                                        :height="220"
                                    />
                                    <p v-else class="text-gray-500 text-center py-8">No hay recomendaciones generadas</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Sección grande completa (Tarjeta C) -->
                    <div class="flex justify-center">
                        <div class="flex flex-col w-full gap-0.5">
                            <!-- Tarjeta Grande C -->
                            <div class="flex-none relative shadow-md bg-white p-6 flex flex-col rounded-xl">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-xl font-semibold text-gray-700">{{ card3Title }}</h2>
                                    <InfoIcon class="w-7 h-7 text-gray-400"/>
                                </div>
                                <div class="mt-1">
                                    <div class="text-5xl font-bold text-blue-800">{{ card3Value }}</div>
                                    <div class="text-sm text-gray-500 my-1">{{ card3Subtitle }}</div>
                                </div>
                            </div>

                            <!-- Contenido Grande C -->
                            <div class="flex-1 relative bg-white shadow-md p-6 rounded-xl mt-2 min-h-[300px]">
                                <!-- ESTUDIANTE: Recomendaciones pendientes -->
                                <template v-if="role === 'Estudiante'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Recomendaciones Recientes</h3>
                                    <div v-if="recommendations && recommendations.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div
                                            v-for="rec in recommendations"
                                            :key="rec.id"
                                            class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border-l-4 border-indigo-500"
                                        >
                                            <p class="font-semibold text-gray-800 text-sm">{{ rec.book_title }}</p>
                                            <p class="text-xs text-gray-600 mt-1">{{ rec.course }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ rec.date }}</p>
                                        </div>
                                    </div>
                                    <p v-else class="text-gray-500 text-center py-8">No tienes recomendaciones pendientes</p>
                                </template>

                                <!-- PROFESOR: Calificaciones pendientes o estadísticas -->
                                <template v-if="role === 'Profesor'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Resumen de Actividad</h3>
                                    <div v-if="stats" class="grid grid-cols-3 gap-4">
                                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                                            <p class="text-3xl font-bold text-blue-800">{{ stats.active_sections || 0 }}</p>
                                            <p class="text-sm text-gray-600 mt-1">Secciones Activas</p>
                                        </div>
                                        <div class="bg-green-50 rounded-lg p-4 text-center">
                                            <p class="text-3xl font-bold text-green-800">{{ stats.total_students || 0 }}</p>
                                            <p class="text-sm text-gray-600 mt-1">Total Estudiantes</p>
                                        </div>
                                        <div class="bg-orange-50 rounded-lg p-4 text-center">
                                            <p class="text-3xl font-bold text-orange-800">{{ stats.pending_grades || 0 }}</p>
                                            <p class="text-sm text-gray-600 mt-1">Calificaciones Pendientes</p>
                                        </div>
                                    </div>
                                    <p v-else class="text-gray-500 text-center py-8">Cargando estadísticas...</p>
                                </template>

                                <!-- ADMIN: Recomendaciones recientes -->
                                <template v-if="role === 'Administrador'">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Recomendaciones Recientes</h3>
                                    <div v-if="recentRecommendations && recentRecommendations.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div
                                            v-for="rec in recentRecommendations"
                                            :key="rec.id"
                                            class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border-l-4 border-indigo-500"
                                        >
                                            <div class="flex justify-between items-start mb-2">
                                                <p class="font-semibold text-gray-800 text-sm flex-1">{{ rec.book_title }}</p>
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs font-semibold ml-2"
                                                    :class="{
                                                        'bg-yellow-100 text-yellow-700': rec.status === 'pending',
                                                        'bg-blue-100 text-blue-700': rec.status === 'viewed',
                                                        'bg-green-100 text-green-700': rec.status === 'completed',
                                                        'bg-gray-100 text-gray-700': rec.status === 'dismissed',
                                                    }"
                                                >
                                                    {{ rec.status }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-600">{{ rec.student }} - {{ rec.course }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ rec.date }}</p>
                                        </div>
                                    </div>
                                    <p v-else class="text-gray-500 text-center py-8">No hay recomendaciones generadas</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
