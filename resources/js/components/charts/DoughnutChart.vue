<script setup lang="ts">
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    ChartOptions,
} from 'chart.js';

// Registrar componentes de Chart.js
ChartJS.register(ArcElement, Tooltip, Legend);

interface Props {
    labels: string[];
    data: number[];
    backgroundColor?: string[];
    height?: number;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    height: 300,
    title: '',
    backgroundColor: () => [
        'rgba(99, 102, 241, 0.8)',   // Indigo
        'rgba(34, 197, 94, 0.8)',    // Green
        'rgba(249, 115, 22, 0.8)',   // Orange
        'rgba(239, 68, 68, 0.8)',    // Red
        'rgba(168, 85, 247, 0.8)',   // Purple
        'rgba(59, 130, 246, 0.8)',   // Blue
    ],
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.data,
            backgroundColor: props.backgroundColor,
            borderColor: props.backgroundColor.map(color => color.replace('0.8', '1')),
            borderWidth: 2,
        },
    ],
}));

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'right',
        },
        title: {
            display: !!props.title,
            text: props.title,
            font: {
                size: 16,
                weight: 'bold',
            },
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgba(99, 102, 241, 0.5)',
            borderWidth: 1,
            callbacks: {
                label: (context) => {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${label}: ${value} (${percentage}%)`;
                },
            },
        },
    },
}));
</script>

<template>
    <div :style="{ height: height + 'px' }">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>
