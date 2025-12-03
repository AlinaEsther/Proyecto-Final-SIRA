<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { ref, computed } from "vue";
import axios from "axios";

const props = defineProps({
    students: {
        type: Array,
        default: () => []
    }
});

const selectedStudent = ref('');
const result = ref(null);
const error = ref(null);
const loading = ref(false);

const sendRequest = async () => {
    error.value = null;
    result.value = null;

    if (!selectedStudent.value) {
        error.value = "Debe seleccionar un estudiante.";
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post("/ai/recommend", {
            student_id: Number(selectedStudent.value)
        });

        if (response.data.status === "success") {
            result.value = response.data;
        } else {
            error.value = response.data.message || response.data.error || "Error desconocido";
        }
    } catch (e) {
        console.error(e);
        error.value = "No se pudo conectar con el servicio IA.";
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <AppLayout title="Recomendaciones IA">
        <div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-semibold mb-4">Recomendaciones generadas por IA</h1>

            <label class="font-medium">Seleccionar estudiante</label>
            <select v-model="selectedStudent" class="border rounded p-2 w-full mt-2">
                <option value="">-- Seleccione un estudiante --</option>
                <option v-for="s in props.students" :key="s.id" :value="s.id">
                    {{ s.name }}
                </option>
            </select>

            <button
                @click="sendRequest"
                :disabled="loading || !selectedStudent"
                class="mt-4 w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700"
            >
                <span v-if="loading">Procesando...</span>
                <span v-else>Generar recomendaciones IA</span>
            </button>

            <div v-if="error" class="mt-4 p-4 bg-red-100 text-red-700 rounded">
                <strong>Error:</strong> {{ error }}
            </div>

            <div v-if="result && result.status === 'success'" class="mt-6">
                <h2 class="text-lg font-bold mb-2">Recomendaciones para {{ result.student.name }}</h2>

                <div v-if="result.recommendations && result.recommendations.length">
                    <div v-for="(rec, idx) in result.recommendations" :key="idx" class="mb-4 p-4 border rounded bg-gray-50">
                        <h3 class="font-semibold">{{ rec.course ?? 'General' }}</h3>
                        <p class="mt-2"><strong>Libro recomendado:</strong> {{ rec.book }}</p>
                        <p v-if="rec.reason" class="mt-2 text-sm text-gray-600"><strong>Por qué:</strong> {{ rec.reason }}</p>

                        <div v-if="rec.activities && rec.activities.length" class="mt-3">
                            <strong>Actividades con bajo rendimiento:</strong>
                            <ul class="list-disc list-inside mt-1">
                                <li v-for="(a,i) in rec.activities" :key="i">
                                    {{ a.title }} — {{ a.score !== null ? a.score : 'N/A' }} {{ a.percentage ? `(${a.percentage}%)` : '' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div v-else class="p-4 bg-green-50 text-green-700 rounded">No hay recomendaciones específicas.</div>
            </div>
        </div>
    </AppLayout>
</template>
