<script setup lang="ts">
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Users, X } from 'lucide-vue-next';
import { computed } from 'vue';

interface Person {
    full_name?: string;
    first_name?: string;
    last_name?: string;
    enrollment_number?: string;
}

interface Student {
    id: number;
    person: Person;
}

interface Props {
    isOpen: boolean;
    students: Student[];
    sectionName?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

const studentCount = computed(() => props.students?.length || 0);

function handleClose() {
    emit('close');
}

function getFullName(person: Person): string {
    return person.full_name || `${person.first_name || ''} ${person.last_name || ''}`.trim() || 'Sin nombre';
}

function getEnrollmentNumber(person: Person): string {
    return person.enrollment_number || 'Sin matrícula';
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="handleClose">
        <DialogContent class="sm:max-w-[600px] bg-white max-h-[80vh] flex flex-col">
            <div class="flex flex-col space-y-6 p-6">
                <!-- Header -->
                <div class="flex items-center gap-3 border-b pb-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 border-2 border-blue-200 flex items-center justify-center">
                        <Users class="w-6 h-6 text-blue-500" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            Estudiantes Inscritos
                        </h2>
                        <p v-if="sectionName" class="text-sm text-gray-600 mt-0.5">
                            {{ sectionName }}
                        </p>
                    </div>
                </div>

                <!-- Student count badge -->
                <div class="flex items-center justify-between bg-blue-50 rounded-lg p-3">
                    <span class="text-sm font-medium text-gray-700">Total de estudiantes:</span>
                    <span class="px-3 py-1 bg-blue-500 text-white text-sm font-bold rounded-full">
                        {{ studentCount }}
                    </span>
                </div>

                <!-- Students list -->
                <div class="flex-1 overflow-y-auto max-h-[400px] space-y-2">
                    <div
                        v-if="!students || students.length === 0"
                        class="text-center text-sm text-gray-500 py-8"
                    >
                        No hay estudiantes inscritos todavía
                    </div>
                    <div
                        v-for="student in students"
                        :key="student.id"
                        class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-semibold">
                                {{ getFullName(student.person).charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ getFullName(student.person) }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ getEnrollmentNumber(student.person) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </DialogContent>
    </Dialog>
</template>
