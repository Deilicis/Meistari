<script setup lang="ts">
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

defineProps<{
    search: string;
    filter: string;
    searchPlaceholder?: string;
    hasActiveFilters: boolean;
}>();

const emit = defineEmits<{
    'update:search': [value: string];
    'update:filter': [value: string];
    'search': [];
    'clear': [];
}>();
</script>

<template>
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 max-w-sm">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
                :value="search"
                @input="emit('update:search', ($event.target as HTMLInputElement).value)"
                @keydown.enter="emit('search')"
                type="text"
                :placeholder="searchPlaceholder ?? 'Meklēt...'"
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
            />
        </div>
        <select
            :value="filter"
            @change="emit('update:filter', ($event.target as HTMLSelectElement).value)"
            class="min-w-[10rem] px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
        >
            <slot name="filter-options" />
        </select>
        <button
            @click="emit('search')"
            class="px-4 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy/90 transition-colors"
        >
            Meklēt
        </button>
        <button
            v-if="hasActiveFilters"
            @click="emit('clear')"
            class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
            Notīrīt
        </button>
    </div>
</template>
