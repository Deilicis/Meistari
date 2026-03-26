import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

export function useConfirmDelete<T>(options: {
    routeName: string;
    getRouteId: (target: T) => number;
    successMessage?: string;
    errorMessage?: string;
}) {
    const {
        routeName,
        getRouteId,
        successMessage = 'Veiksmīgi izdzēsts!',
        errorMessage = 'Neizdevās izdzēst.',
    } = options;

    const deleteTarget = ref<T | null>(null);
    const isDeleting = ref(false);

    const confirmDelete = (target: T) => { deleteTarget.value = target; };
    const cancelDelete = () => { deleteTarget.value = null; };

    const executeDelete = () => {
        if (!deleteTarget.value) return;
        isDeleting.value = true;
        router.delete(route(routeName, getRouteId(deleteTarget.value)), {
            preserveScroll: true,
            onSuccess: () => toast.success(successMessage),
            onError: () => toast.error(errorMessage),
            onFinish: () => { isDeleting.value = false; deleteTarget.value = null; },
        });
    };

    return { deleteTarget, isDeleting, confirmDelete, cancelDelete, executeDelete };
}
