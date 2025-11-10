import { onMounted } from 'vue';

export function initializeTheme() {
    if (typeof window === 'undefined') return;

    // Elimina clase 'dark' si la hubiera, y asegura 'light'
    document.documentElement.classList.remove('dark');
    document.documentElement.classList.add('light');
}

export function useAppearance() {
    onMounted(() => {
        initializeTheme();
    });

    return {
        appearance: 'light',
        updateAppearance: () => {
            // Ignorado. Solo modo claro permitido.
        },
    };
}
