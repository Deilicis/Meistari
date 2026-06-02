<script setup lang="ts">
import { FunnelIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useDebouncedFilter } from '@/composables/useDebouncedFilter';

const props = defineProps<{
    filters: { action?: string; type?: string };
}>();

const { filterForm, clearFilters, hasActiveFilters } = useDebouncedFilter(
    'admin.audit-logs.index',
    { action: props.filters.action ?? '', type: props.filters.type ?? '' },
    { action: '', type: '' },
);
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3 flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-1.5 text-gray-400 shrink-0">
            <FunnelIcon class="w-4 h-4" />
            <span class="text-xs font-semibold uppercase tracking-widest">Filtri</span>
        </div>

        <div class="w-px h-5 bg-gray-200 shrink-0" />

        <select
            v-model="filterForm.action"
            class="w-48 px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy transition"
        >
            <option value="">Visas darbības</option>
            <option value="created">Izveidots</option>
            <option value="updated">Atjaunināts</option>
            <option value="deleted">Dzēsts</option>
        </select>

        <input
            v-model="filterForm.type"
            type="text"
            placeholder="Objekta tips (piem. Service)"
            class="w-56 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy transition"
        />

        <button
            v-if="hasActiveFilters()"
            @click="clearFilters"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
        >
            <XMarkIcon class="w-3.5 h-3.5" />
            Notīrīt
        </button>
    </div>
</template>
