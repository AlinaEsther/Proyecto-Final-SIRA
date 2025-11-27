import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export type AlertType = 'success' | 'warning' | 'danger';

export interface Alert {
    type: AlertType;
    message: string;
}

export function useAlert() {
    const page = usePage();

    const alert = computed<Alert | null>(() => {
        return (page.props.alert as Alert) || null;
    });

    return {
        alert
    };
}
