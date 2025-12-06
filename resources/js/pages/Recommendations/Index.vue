<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    students: { type: Array, default: () => [] },
    isStudent: { type: Boolean, default: false },
    student: { type: Object, default: null },
    courses: { type: Array, default: () => [] }
});

// Si es estudiante → selecciona su propio ID
const selectedStudent = ref(
    props.isStudent && props.student ? props.student.id : ""
);

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
            error.value = response.data.message || "Ocurrió un error.";
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
        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <h1 class="text-2xl font-semibold text-gray-800 mb-6">
                        Recomendaciones generadas por IA
                    </h1>

                    <!-- SOLO PARA PROFESORES Y ADMIN -->
                    <div v-if="!props.isStudent" class="mb-4">
                        <label class="font-medium text-gray-700">Seleccionar estudiante</label>
                        <select
                            v-model="selectedStudent"
                            class="border rounded p-2 w-full mt-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">-- Seleccione un estudiante --</option>
                            <option v-for="s in props.students" :key="s.id" :value="s.id">
                                {{ s.name }}
                            </option>
                        </select>
                    </div>

                    <!-- BOTÓN GENERAR -->
                    <button
                        @click="sendRequest"
                        :disabled="loading || !selectedStudent"
                        class="mt-2 w-full bg-blue-600 text-white py-2 rounded-lg shadow hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="loading">Procesando...</span>
                        <span v-else>Generar recomendaciones IA</span>
                    </button>

                    <!-- ERRORES -->
                    <div
                        v-if="error"
                        class="mt-4 p-4 bg-red-100 text-red-700 rounded border border-red-300"
                    >
                        <strong>Error:</strong> {{ error }}
                    </div>

                    <!-- RESULTADOS -->
                    <div v-if="result && result.status === 'success'" class="mt-8">
                        <h2 class="text-xl font-bold text-gray-700 mb-4">
                            Recomendaciones para {{ result.student.name }}
                        </h2>

                        <!-- LISTA DE RECOMENDACIONES -->
                        <div
                            v-for="(rec, idx) in result.recommendations"
                            :key="idx"
                            class="mb-6 p-4 border rounded-lg bg-gray-50 shadow-sm"
                        >
                            <h3 class="font-semibold text-gray-800">
                                {{ rec.course ?? 'General' }}
                            </h3>

                            <p class="mt-2">
                                <strong>Libro recomendado:</strong> {{ rec.book }}
                            </p>

                            <p v-if="rec.reason" class="mt-2 text-sm text-gray-600">
                                <strong>Por qué:</strong> {{ rec.reason }}
                            </p>

                            <div v-if="rec.activities?.length" class="mt-3">
                                <strong>Actividades con bajo rendimiento:</strong>
                                <ul class="list-disc list-inside mt-1">
                                    <li
                                        v-for="(a,i) in rec.activities"
                                        :key="i"
                                        class="text-gray-700"
                                    >
                                        {{ a.title }} — {{ a.score ?? 'N/A' }} 
                                        {{ a.percentage ? `(${a.percentage}%)` : "" }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- SIN RECOMENDACIONES -->
                        <div
                            v-if="!result.recommendations.length"
                            class="p-4 bg-green-50 text-green-700 border border-green-300 rounded"
                        >
                            No hay recomendaciones específicas.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
