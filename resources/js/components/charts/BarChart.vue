<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ChartOptions,
} from 'chart.js';

// Registrar componentes de Chart.js
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

interface Props {
    labels: string[];
    datasets: Array<{
        label: string;
        data: number[];
        backgroundColor?: string | string[];
        borderColor?: string | string[];
        borderWidth?: number;
    }>;
    height?: number;
    title?: string;
    horizontal?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    height: 300,
    title: '',
    horizontal: false,
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: props.datasets.map((dataset) => ({
        ...dataset,
        backgroundColor: dataset.backgroundColor || 'rgba(99, 102, 241, 0.8)',
        borderColor: dataset.borderColor || 'rgba(99, 102, 241, 1)',
        borderWidth: dataset.borderWidth !== undefined ? dataset.borderWidth : 1,
    })),
}));

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: props.horizontal ? 'y' : 'x',
    plugins: {
        legend: {
            display: true,
            position: 'top',
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
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
            },
        },
        x: {
            grid: {
                display: false,
            },
        },
    },
}));
</script>

<template>
    <div :style="{ height: height + 'px' }">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
