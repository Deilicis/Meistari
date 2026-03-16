<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import * as HeroIcons from '@heroicons/vue/24/outline';
import type { Category } from '@/types/models';

const props = defineProps<{
    category: Category & { services_count: number };
}>();

const iconComponent = computed(() => {
    if (!props.category.icon) return null;
    return (HeroIcons as Record<string, unknown>)[props.category.icon] ?? null;
});
</script>

<template>
    <Link
        :href="route('seeker.services.index', { category_id: category.id })"
        class="group flex flex-col items-center justify-center gap-3 rounded-2xl bg-white border border-gray-100 p-6 text-center shadow-sm hover:shadow-md hover:border-navy/20 hover:-translate-y-0.5 transition-all duration-200"
    >
        <div class="w-14 h-14 rounded-2xl bg-navy/5 flex items-center justify-center group-hover:bg-navy/10 transition-colors">
            <component
                :is="iconComponent"
                v-if="iconComponent"
                class="w-7 h-7 text-navy/60"
            />
            <svg v-else class="w-7 h-7 text-navy/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
        </div>

        <div>
            <p class="font-semibold text-navy text-sm leading-snug">{{ category.name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ category.services_count }} pakalpojumi</p>
        </div>
    </Link>
</template>
