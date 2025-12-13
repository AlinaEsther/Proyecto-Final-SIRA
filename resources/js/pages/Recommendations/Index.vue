<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import BaseSelect from "@/components/BaseSelect.vue";
import RecommendationHistoryModal from "@/components/Modals/RecommendationHistoryModal.vue";
import PendingRequestsModal from "@/components/Modals/PendingRequestsModal.vue";
import type { BreadcrumbItem } from "@/types";
import { ref, computed, watch } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { Brain, BookOpen, TrendingUp, Users, Star, CheckCircle, GraduationCap, Sparkles, Loader2, AlertTriangle, PartyPopper, ExternalLink, Plus, Clock } from "lucide-vue-next";

interface Student {
    id: number;
    name: string;
}

interface Section {
    id: number;
    name: string;
    course_name: string;
    students: Student[];
}

interface Statistics {
    total_recommendations?: number;
    this_month?: number;
    completed?: number;
    pending?: number;
    pending_requests?: number;
    completion_rate?: number;
    students_helped?: number;
    average_relevance?: number;
    total_students?: number;
    by_status?: {
        pending: number;
        viewed: number;
        completed: number;
        dismissed: number;
    };
}

interface HistoryRecommendation {
    id: number;
    book_title: string;
    course_name: string;
    status: string;
    created_at: string;
    material_id?: number;
}

const props = defineProps<{
    students?: Student[];
    isStudent: boolean;
    student?: { id: number; name: string } | null;
    sections?: Section[];
    userRole?: string;
    statistics?: Statistics;
    history?: HistoryRecommendation[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Recomendaciones IA", href: "" },
];

const selectedSection = ref<number | ''>('');
const selectedStudent = ref<number | ''>(props.student?.id ?? '');
const result = ref<any>(null);
const error = ref<string | null>(null);
const loading = ref(false);
const statistics = ref<Statistics>(props.statistics || {});
const recommendationHistory = ref<HistoryRecommendation[]>(props.history || []);
const showHistory = ref(false); // Toggle para mostrar/ocultar historial
const showPendingRequests = ref(false); // Modal de solicitudes pendientes
const pendingRequests = ref<any[]>([]); // Lista de solicitudes pendientes

// Filtrar estudiantes según sección seleccionada
const filteredStudents = computed(() => {
    // Si hay secciones disponibles, DEBE seleccionar una sección primero
    if (props.sections && props.sections.length > 0) {
        if (!selectedSection.value) {
            return []; // No mostrar estudiantes hasta que seleccione una sección
        }
        const section = props.sections.find(s => s.id === selectedSection.value);
        return section?.students || [];
    }
    // Fallback para casos sin secciones (no debería pasar en Admin/Profesor)
    return props.students || [];
});

// Cuando cambia la sección, resetear estudiante seleccionado
watch(selectedSection, () => {
    if (!props.isStudent) {
        selectedStudent.value = '';
        result.value = null;
    }
});

const sendRequest = async () => {
    error.value = null;
    result.value = null;

    if (!props.isStudent && !selectedStudent.value) {
        error.value = "Debe seleccionar un estudiante.";
        return;
    }

    loading.value = true;

    try {
        // Construir payload - Incluir section_id si está seleccionada (Admin/Profesor)
        const payload: any = {
            student_id: Number(selectedStudent.value)
        };

        // Solo enviar section_id si NO es estudiante Y hay sección seleccionada
        if (!props.isStudent && selectedSection.value) {
            payload.section_id = Number(selectedSection.value);
        }

        const response = await axios.post("/ai/recommend", payload);

        if (response.data.status === "success") {
            result.value = response.data;
            // NO establecer error cuando no hay recomendaciones pero el rendimiento es bueno
            // El mensaje de éxito se mostrará en el template

            // Recargar estadísticas después de generar recomendaciones
            // Esperar 1 segundo para dar tiempo a que la BD se actualice
            setTimeout(async () => {
                await loadStatistics();
                console.log("Estadísticas después de generar:", statistics.value);

                // Forzar recarga completa desde el servidor
                router.reload({ only: ['statistics'] });
            }, 1000);

            // Recargar histórico si es estudiante
            if (props.isStudent) {
                await loadHistory();
            }
        } else {
            error.value = response.data.message || "Ocurrió un error.";
        }
    } catch (e: any) {
        console.error(e);
        error.value = e.response?.data?.message || "No se pudo conectar con el servicio IA.";
    } finally {
        loading.value = false;
    }
};

const loadStatistics = async () => {
    try {
        const response = await axios.get("/api/ai/statistics");
        statistics.value = response.data;
        console.log("Estadísticas actualizadas:", statistics.value); // Debug
    } catch (e) {
        console.error("Error cargando estadísticas:", e);
    }
};

const loadHistory = async () => {
    if (!props.student?.id) return;

    try {
        const response = await axios.get(`/ai/recommendations/history/${props.student.id}`);
        recommendationHistory.value = response.data.recommendations.data || [];
    } catch (e) {
        console.error("Error cargando histórico:", e);
    }
};

const loadPendingRequests = async () => {
    try {
        const response = await axios.get('/material-requests/pending');
        pendingRequests.value = response.data;
    } catch (e) {
        console.error("Error cargando solicitudes pendientes:", e);
    }
};

const showPendingRequestsModal = async () => {
    // Si es Admin, navegar directamente al tab de solicitudes en /materials
    if (props.userRole === 'Administrador') {
        router.visit('/materials?tab=solicitudes');
        return;
    }

    // Para profesores (aunque ya no deberían ver esto), mostrar modal
    await loadPendingRequests();
    showPendingRequests.value = true;
};

const getPerformanceColor = (percentage: number) => {
    if (percentage < 60) return 'text-red-600 bg-red-50';
    if (percentage < 75) return 'text-yellow-600 bg-yellow-50';
    return 'text-green-600 bg-green-50';
};

const getProgressBarColor = (percentage: number) => {
    if (percentage < 60) return 'bg-red-500';
    if (percentage < 75) return 'bg-yellow-500';
    return 'bg-green-500';
};

const requestingMaterial = ref<number | null>(null);

const requestMaterial = (recommendation: any) => {
    if (requestingMaterial.value === recommendation.id) return;

    requestingMaterial.value = recommendation.id;

    const bookTitle = recommendation.book_title || recommendation.book || 'Material recomendado';
    const bookAuthor = recommendation.book_author || null;

    router.post('/material-requests/create', {
        recommendation_id: recommendation.id,
        title: bookTitle,
        author: bookAuthor,
        type_requested: 'link',
        description: `Recomendación IA para ${recommendation.course_name || recommendation.course}`,
    }, {
        preserveScroll: true,
        onFinish: () => {
            requestingMaterial.value = null;
        }
    });
};

// Función para mostrar el historial cuando se hace click en los widgets
const showHistoryPanel = async () => {
    // Si es estudiante y no hay historial cargado, cargarlo primero
    if (props.isStudent && !recommendationHistory.value.length) {
        await loadHistory();
    }
    // Abrir el modal
    showHistory.value = true;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" class="overflow-hidden">
        <div class="w-full h-full flex flex-col gap-6">
            <!-- Encabezado -->
            <div class="p-6 border-b bg-white rounded-[15px] shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Recomendaciones IA
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ props.isStudent ? 'Visualiza tus recomendaciones personalizadas' : 'Genera recomendaciones basadas en el rendimiento académico' }}
                        </p>
                    </div>
                    <div v-if="props.userRole" class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">
                            {{ props.userRole }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white p-6 rounded-[15px] shadow-sm">
                <!-- Mensaje cuando no hay datos -->
                <div v-if="!statistics || !statistics.total_recommendations || statistics.total_recommendations === 0" class="text-center py-8">
                    <Brain :size="64" class="mx-auto text-gray-300 mb-4" />
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No hay recomendaciones generadas aún</h3>
                    <p class="text-sm text-gray-500">
                        {{ props.isStudent
                            ? 'Genera tu primera recomendación para ver estadísticas personalizadas'
                            : 'Genera recomendaciones para tus estudiantes para ver estadísticas aquí'
                        }}
                    </p>
                </div>

                <!-- Widgets de estadísticas cuando hay datos -->
                <div v-else>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Estadísticas de Recomendaciones IA</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Total de Recomendaciones - CLICKEABLE -->
                        <div
                            @click="showHistoryPanel"
                            class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-[15px] hover:shadow-lg transition-all cursor-pointer hover:scale-105"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-blue-600 font-medium">Total Generadas</p>
                                    <p class="text-3xl font-bold text-blue-900">{{ statistics.total_recommendations || 0 }}</p>
                                    <p class="text-xs text-blue-600 mt-1 flex items-center gap-1">
                                        {{ statistics.this_month || 0 }} este mes
                                        <span class="text-[10px]">👆 Click para ver</span>
                                    </p>
                                </div>
                                <BookOpen :size="40" class="text-blue-600 opacity-80" />
                            </div>
                        </div>

                        <!-- Estudiantes / Completadas -->
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-[15px] hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-purple-600 font-medium">
                                        {{ props.isStudent ? 'Completadas' : 'Estudiantes Atendidos' }}
                                    </p>
                                    <p class="text-3xl font-bold text-purple-900">
                                        {{ statistics.completed || statistics.students_helped || statistics.total_students || 0 }}
                                    </p>
                                    <p v-if="!props.isStudent" class="text-xs text-purple-600 mt-1">Únicos</p>
                                    <p v-else class="text-xs text-purple-600 mt-1">De {{ statistics.total_recommendations }} totales</p>
                                </div>
                                <component :is="props.isStudent ? CheckCircle : Users" :size="40" class="text-purple-600 opacity-80" />
                            </div>
                        </div>

                        <!-- Relevancia / Tasa de Completadas -->
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-[15px] hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-orange-600 font-medium">
                                        {{ props.isStudent ? 'Tasa de Completadas' : 'Relevancia Promedio' }}
                                    </p>
                                    <p class="text-3xl font-bold text-orange-900">
                                        {{ props.isStudent
                                            ? (statistics.completion_rate || 0) + '%'
                                            : (statistics.average_relevance || 0)
                                        }}
                                    </p>
                                    <p class="text-xs text-orange-600 mt-1">
                                        {{ props.isStudent ? 'Completadas vs Total' : 'De 100 puntos' }}
                                    </p>
                                </div>
                                <Star :size="40" class="text-orange-600 opacity-80" />
                            </div>
                        </div>

                        <!-- Pendientes (Estudiantes) / Solicitudes Pendientes (Admin solamente) -->
                        <div
                            @click="props.userRole === 'Administrador' ? showPendingRequestsModal() : undefined"
                            :class="[
                                'bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-[15px] hover:shadow-md transition-shadow',
                                props.userRole === 'Administrador' ? 'cursor-pointer hover:scale-105 transition-transform' : ''
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-yellow-700 font-medium">
                                        {{ props.isStudent ? 'Pendientes' : (props.userRole === 'Administrador' ? 'Solicitudes Pendientes' : 'Pendientes') }}
                                    </p>
                                    <p class="text-3xl font-bold text-yellow-900">
                                        {{ props.isStudent || props.userRole !== 'Administrador'
                                            ? (statistics.pending || 0)
                                            : (statistics.pending_requests || 0)
                                        }}
                                    </p>
                                    <p class="text-xs text-yellow-700 mt-1">
                                        {{ props.isStudent ? 'Por revisar' : (props.userRole === 'Administrador' ? 'Materiales por aprobar' : 'Por revisar') }}
                                    </p>
                                </div>
                                <BookOpen :size="40" class="text-yellow-600 opacity-80" />
                            </div>
                        </div>
                    </div>

                    <!-- Desglose por estado (solo para Admin) -->
                    <div v-if="!props.isStudent && statistics.by_status" class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Desglose por Estado de Recomendaciones</h4>
                        <div class="grid grid-cols-5 gap-3">
                            <div class="text-center bg-white rounded-lg p-3">
                                <div class="text-2xl font-bold text-purple-600">{{ statistics.by_status.not_requested || 0 }}</div>
                                <div class="text-xs text-gray-600 mt-1">No Solicitadas</div>
                            </div>
                            <div class="text-center bg-white rounded-lg p-3">
                                <div class="text-2xl font-bold text-yellow-600">{{ statistics.by_status.pending }}</div>
                                <div class="text-xs text-gray-600 mt-1">Pendientes</div>
                            </div>
                            <div class="text-center bg-white rounded-lg p-3">
                                <div class="text-2xl font-bold text-blue-600">{{ statistics.by_status.viewed }}</div>
                                <div class="text-xs text-gray-600 mt-1">Vistas</div>
                            </div>
                            <div class="text-center bg-white rounded-lg p-3">
                                <div class="text-2xl font-bold text-green-600">{{ statistics.by_status.completed }}</div>
                                <div class="text-xs text-gray-600 mt-1">Completadas</div>
                            </div>
                            <div class="text-center bg-white rounded-lg p-3">
                                <div class="text-2xl font-bold text-gray-600">{{ statistics.by_status.dismissed }}</div>
                                <div class="text-xs text-gray-600 mt-1">Descartadas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Solicitudes de Materiales Pendientes (solo Admin) -->
                    <div v-if="props.userRole === 'Administrador' && statistics.pending_requests !== undefined" class="mt-6">
                        <div
                            @click="showPendingRequestsModal"
                            class="bg-gradient-to-br from-amber-50 to-orange-100 p-6 rounded-[15px] border-2 border-orange-200 cursor-pointer hover:shadow-xl hover:scale-[1.02] transition-all"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="bg-orange-500 p-3 rounded-xl">
                                        <BookOpen :size="32" class="text-white" />
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-orange-900">Solicitudes de Materiales</h4>
                                        <p class="text-sm text-orange-700 mt-1">Click para ir a solicitudes</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-4xl font-bold text-orange-600">{{ statistics.pending_requests }}</div>
                                    <div class="text-sm text-orange-700 font-medium">Pendientes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card informativa para estudiantes -->
            <div v-if="props.isStudent" class="bg-gradient-to-br from-blue-500 to-indigo-600 p-8 rounded-[15px] shadow-lg text-white">
                <div class="flex items-center gap-6">
                    <GraduationCap :size="64" class="text-white flex-shrink-0" />
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold mb-2">¡Obtén Recomendaciones Personalizadas!</h2>
                        <p class="text-blue-100 mb-4">
                            Nuestro sistema de IA analiza tu rendimiento académico y te sugiere recursos educativos específicos para mejorar en las áreas donde más lo necesitas.
                        </p>
                        <button
                            @click="sendRequest"
                            :disabled="loading"
                            class="flex items-center gap-2 px-6 py-3 bg-white text-indigo-600 rounded-full font-semibold hover:bg-blue-50 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <Loader2 v-if="loading" :size="20" class="animate-spin" />
                            <Sparkles v-else :size="20" />
                            <span>{{ loading ? 'Generando...' : 'Generar Recomendaciones' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal para Profesores/Admin -->
            <div v-else class="bg-white p-6 rounded-[15px] shadow-sm">
                <!-- Selector de Sección (Admin/Profesor) -->
                <div v-if="props.sections && props.sections.length > 0" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Seleccionar Sección
                    </label>
                    <BaseSelect
                        v-model="selectedSection"
                        :options="props.sections"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="-- Seleccione una sección --"
                        :filter="true"
                        filterPlaceholder="Buscar sección..."
                    />
                </div>

                <!-- Selector de Estudiante -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Seleccionar Estudiante
                    </label>
                    <BaseSelect
                        v-model="selectedStudent"
                        :options="filteredStudents"
                        optionLabel="name"
                        optionValue="id"
                        :placeholder="!selectedSection && props.sections && props.sections.length > 0
                            ? '-- Primero seleccione una sección --'
                            : '-- Seleccione un estudiante --'"
                        :filter="true"
                        filterPlaceholder="Buscar estudiante..."
                        :disabled="props.sections && props.sections.length > 0 && !selectedSection"
                    />
                    <p v-if="props.sections && props.sections.length > 0 && !selectedSection" class="text-xs text-orange-600 mt-1 font-medium">
                        ⚠️ Debe seleccionar una sección primero para ver los estudiantes
                    </p>
                    <p v-else-if="selectedSection && filteredStudents.length === 0" class="text-xs text-gray-500 mt-1">
                        Esta sección no tiene estudiantes inscritos
                    </p>
                    <p v-else-if="selectedSection && filteredStudents.length > 0" class="text-xs text-green-600 mt-1">
                        ✓ {{ filteredStudents.length }} estudiante(s) disponible(s) en esta sección
                    </p>
                </div>

                <!-- Botón de acción -->
                <button
                    @click="sendRequest"
                    :disabled="loading || !selectedStudent"
                    class="flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-6 py-3 font-semibold text-white transition-all duration-300 hover:bg-indigo-700 hover:scale-[1.02] focus:scale-[1] disabled:bg-gray-300 disabled:cursor-not-allowed disabled:hover:scale-100"
                >
                    <Loader2 v-if="loading" :size="20" class="animate-spin" />
                    <Sparkles v-else :size="20" />
                    <span>{{ loading ? 'Procesando...' : 'Generar Recomendaciones' }}</span>
                </button>
            </div>

            <!-- Mensajes de error -->
            <div v-if="error" class="p-4 bg-red-50 text-red-700 rounded-[15px] border border-red-200 flex items-start gap-3">
                <AlertTriangle :size="24" class="flex-shrink-0" />
                <div>
                    <strong>Error:</strong> {{ error }}
                </div>
            </div>

            <!-- Resultados -->
            <div v-if="result && result.status === 'success' && result.recommendations && result.recommendations.length > 0" class="bg-white rounded-[15px] shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <BookOpen :size="28" class="text-indigo-600" />
                    Recomendaciones para {{ result.student.name }}
                </h2>

                <div class="space-y-4">
                    <div v-for="(rec, idx) in result.recommendations" :key="idx" class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-[15px] p-8 shadow-md">
                        <div class="flex items-start gap-6">
                            <BookOpen :size="48" class="text-indigo-600 flex-shrink-0" />
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-indigo-900 mb-2">
                                    {{ rec.book_title || rec.book }}
                                </h3>
                                <p v-if="rec.book_author" class="text-indigo-600 mb-2">
                                    <strong>Autor:</strong> {{ rec.book_author }}
                                </p>
                                <p class="text-indigo-700 mb-4"><strong>Curso:</strong> {{ rec.course ?? 'General' }}</p>

                                <!-- Razón -->
                                <div v-if="rec.reason" class="bg-white bg-opacity-70 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-gray-700">
                                        <strong class="text-indigo-900 flex items-center gap-2">
                                            <Brain :size="18" />
                                            Por qué este libro:
                                        </strong>
                                        <span class="block mt-1">{{ rec.reason }}</span>
                                    </p>
                                </div>

                                <!-- Actividades con bajo rendimiento -->
                                <div v-if="rec.activities && rec.activities.length" class="mt-4">
                                    <p class="font-bold text-indigo-900 mb-3 flex items-center gap-2">
                                        <TrendingUp :size="20" />
                                        Actividades con Bajo Rendimiento:
                                    </p>
                                    <div class="space-y-2">
                                        <div
                                            v-for="(a, i) in rec.activities"
                                            :key="i"
                                            class="bg-white rounded-lg p-3 flex items-center justify-between"
                                        >
                                            <span class="text-gray-800 font-medium">{{ a.title }}</span>
                                            <div class="flex items-center gap-3">
                                                <span class="text-gray-600">
                                                    {{ a.score !== null ? a.score : 'N/A' }}
                                                </span>
                                                <span
                                                    v-if="a.percentage"
                                                    :class="['px-3 py-1 rounded-full text-sm font-bold', getPerformanceColor(a.percentage)]"
                                                >
                                                    {{ a.percentage }}%
                                                </span>
                                                <!-- Progress bar -->
                                                <div v-if="a.percentage" class="w-24 bg-gray-200 rounded-full h-2">
                                                    <div
                                                        :class="['h-2 rounded-full transition-all', getProgressBarColor(a.percentage)]"
                                                        :style="{ width: a.percentage + '%' }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones de acción según el rol -->
                                <div class="mt-6 flex items-center gap-3">
                                    <!-- Botón para Estudiantes: Ver material si existe -->
                                    <button
                                        v-if="props.isStudent && rec.material_id"
                                        @click="router.visit(`/materials/${rec.material_id}`)"
                                        class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-full font-semibold hover:bg-green-700 transition-all transform hover:scale-105"
                                    >
                                        <ExternalLink :size="18" />
                                        <span>Ver Material</span>
                                    </button>

                                    <!-- Botón para Admin: Agregar directamente a materiales -->
                                    <button
                                        v-if="!props.isStudent && props.userRole === 'Administrador'"
                                        @click="router.visit(`/materials/create?title=${encodeURIComponent(rec.book_title || rec.book)}&author=${encodeURIComponent(rec.book_author || '')}&type=link`)"
                                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-full font-semibold hover:bg-indigo-700 transition-all transform hover:scale-105"
                                    >
                                        <Plus :size="18" />
                                        <span>Agregar a Materiales</span>
                                    </button>

                                    <!-- Botón para Profesor/Estudiante: Solicitar material (solo si NO hay material y NO hay solicitud pendiente) -->
                                    <button
                                        v-if="(props.isStudent && !rec.material_id && !rec.material_request_id) || (!props.isStudent && props.userRole !== 'Administrador' && !rec.material_id && !rec.material_request_id)"
                                        @click="requestMaterial(rec)"
                                        :disabled="requestingMaterial === rec.id"
                                        class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <Loader2 v-if="requestingMaterial === rec.id" :size="18" class="animate-spin" />
                                        <Plus v-else :size="18" />
                                        <span>{{ requestingMaterial === rec.id ? 'Solicitando...' : 'Solicitar Material' }}</span>
                                    </button>

                                    <!-- Badge: Material solicitado (pendiente de aprobación) -->
                                    <div v-if="rec.material_request_id && !rec.material_id && rec.status === 'pending'" class="flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full font-semibold">
                                        <Clock :size="18" />
                                        <span>Material Solicitado - En Revisión</span>
                                    </div>

                                    <!-- Badge: Material disponible -->
                                    <div v-if="rec.material_id && rec.status === 'completed'" class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">
                                        <CheckCircle :size="18" />
                                        <span>Material Disponible</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sin recomendaciones pero éxito -->
            <div v-else-if="result && result.status === 'success' && (!result.recommendations || result.recommendations.length === 0)" class="bg-white rounded-[15px] shadow-sm p-6">
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-8 rounded-[15px]">
                    <div class="flex items-center gap-4">
                        <PartyPopper :size="64" class="text-green-600 flex-shrink-0" />
                        <div>
                            <h3 class="text-2xl font-bold text-green-900 mb-2">¡Excelente Rendimiento!</h3>
                            <p class="text-green-700">
                                {{ result.message || 'El estudiante tiene buen rendimiento general (≥80%). ¡Sigue así!' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Historial -->
        <RecommendationHistoryModal
            :isOpen="showHistory"
            :recommendations="recommendationHistory"
            :isStudent="props.isStudent"
            @close="showHistory = false"
        />

        <!-- Modal de Solicitudes Pendientes -->
        <PendingRequestsModal
            :isOpen="showPendingRequests"
            :requests="pendingRequests"
            @close="showPendingRequests = false"
        />
    </AppLayout>
</template>
