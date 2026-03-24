import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export function useDebouncedFilter<T extends Record<string, string>>(
    routeName: string,
    initialFilters: T,
    emptyFilters: T,
    delay = 300
) {
    const filterForm = ref<T>({ ...initialFilters });
    let searchTimeout: ReturnType<typeof setTimeout>;

    watch(filterForm, (newFilters) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            router.get(route(routeName), newFilters, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, delay);
    }, { deep: true });

    const clearFilters = () => { filterForm.value = { ...emptyFilters }; };
    const hasActiveFilters = () => Object.values(filterForm.value).some(v => v !== '');

    return { filterForm, clearFilters, hasActiveFilters };
}
