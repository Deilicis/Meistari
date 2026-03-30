import { ref, computed, type ComputedRef } from 'vue';
import { router } from '@inertiajs/vue3';
import type { WelcomeFilters, PopularCategory } from '@/types/welcome';

export function useWelcomeFilters(filtersRef: ComputedRef<WelcomeFilters>) {
    const searchInput = ref(filtersRef.value.search ?? '');
    const activeTab = ref<'services' | 'jobs'>(filtersRef.value.tab === 'jobs' ? 'jobs' : 'services');

    const activeTagId = computed(() =>
        filtersRef.value.category_id ? Number(filtersRef.value.category_id) : null
    );

    const submitSearch = () => {
        router.get(route('home'), {
            search: searchInput.value || undefined,
            category_id: filtersRef.value.category_id || undefined,
            tab: activeTab.value,
        }, { preserveScroll: true });
    };

    const selectTag = (tag: PopularCategory) => {
        const newId = tag.id !== activeTagId.value ? tag.id : undefined;
        router.get(route('home'), {
            search: searchInput.value || undefined,
            category_id: newId,
            tab: activeTab.value,
        }, { preserveScroll: true });
    };

    const switchTab = (tab: 'services' | 'jobs') => {
        activeTab.value = tab;
        router.get(route('home'), {
            search: searchInput.value || undefined,
            category_id: filtersRef.value.category_id || undefined,
            tab,
        }, { preserveScroll: true });
    };

    const clearFilters = () => {
        router.get(route('home'), { tab: activeTab.value }, { preserveScroll: true });
    };

    return { searchInput, activeTab, activeTagId, submitSearch, selectTag, switchTab, clearFilters };
}
