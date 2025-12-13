<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface Statistics {
    total_recommendations: number;
    this_month: number;
    completed?: number;
    pending?: number;
    students_helped?: number;
    total_students?: number;
    average_relevance: number;
    completion_rate?: number;
    by_status?: {
        pending: number;
        viewed: number;
        completed: number;
        dismissed: number;
    };
}

const statistics = ref<Statistics | null>(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/api/ai/statistics');
        statistics.value = response.data;
    } catch (error) {
        console.error('Error loading statistics:', error);
    } finally {
        loading.value = false;
    }
});

const getCompletionPercentage = () => {
    if (!statistics.value?.by_status) return 0;
    const total = statistics.value.total_recommendations;
    if (total === 0) return 0;
    return Math.round((statistics.value.by_status.completed / total) * 100);
};
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Estadísticas de Recomendaciones IA</h3>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div v-for="i in 4" :key="i" class="animate-pulse bg-gray-200 h-24 rounded-lg"></div>
        </div>

        <div v-else-if="statistics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total de Recomendaciones -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">Total Generadas</p>
                        <p class="text-3xl font-bold text-blue-900 mt-1">
                            {{ statistics.total_recommendations }}
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            {{ statistics.this_month }} este mes
                        </p>
                    </div>
                    <div class="text-4xl">📊</div>
                </div>
            </div>

            <!-- Estudiantes Atendidos -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600">Estudiantes</p>
                        <p class="text-3xl font-bold text-purple-900 mt-1">
                            {{ statistics.students_helped || statistics.total_students || 0 }}
                        </p>
                        <p class="text-xs text-purple-700 mt-1">
                            Únicos atendidos
                        </p>
                    </div>
                    <div class="text-4xl">👥</div>
                </div>
            </div>

            <!-- Promedio de Relevancia -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-orange-600">Relevancia</p>
                        <p class="text-3xl font-bold text-orange-900 mt-1">
                            {{ statistics.average_relevance }}<span class="text-xl">/100</span>
                        </p>
                        <p class="text-xs text-orange-700 mt-1">
                            Promedio de calidad
                        </p>
                    </div>
                    <div class="text-4xl">⭐</div>
                </div>
            </div>

            <!-- Tasa de Completadas -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">Tasa Completadas</p>
                        <p class="text-3xl font-bold text-green-900 mt-1">
                            {{ statistics.completion_rate || getCompletionPercentage() }}%
                        </p>
                        <p class="text-xs text-green-700 mt-1">
                            {{ statistics.completed || statistics.by_status?.completed || 0 }} completadas
                        </p>
                    </div>
                    <div class="text-4xl">✅</div>
                </div>
                <!-- Progress ring -->
                <div class="mt-3">
                    <div class="w-full bg-green-200 rounded-full h-2">
                        <div
                            class="bg-green-600 h-2 rounded-full transition-all duration-500"
                            :style="{ width: (statistics.completion_rate || getCompletionPercentage()) + '%' }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desglose por Estado -->
        <div v-if="statistics?.by_status" class="bg-white rounded-lg p-5 shadow-sm">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Desglose por Estado</h4>
            <div class="grid grid-cols-4 gap-3">
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ statistics.by_status.pending }}</div>
                    <div class="text-xs text-gray-600">Pendientes</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ statistics.by_status.viewed }}</div>
                    <div class="text-xs text-gray-600">Vistas</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ statistics.by_status.completed }}</div>
                    <div class="text-xs text-gray-600">Completadas</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-600">{{ statistics.by_status.dismissed }}</div>
                    <div class="text-xs text-gray-600">Descartadas</div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
            No hay datos disponibles
        </div>
    </div>
</template>

