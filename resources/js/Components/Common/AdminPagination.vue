<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: { url: string | null; label: string; active: boolean }[];
}>();
</script>

<template>
    <div class="flex items-center justify-end gap-1 px-6 py-3 border-t border-gray-100">
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                class="px-3 py-1.5 rounded-lg border transition-colors"
                :class="link.active
                    ? 'bg-navy text-white border-navy font-semibold'
                    : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                v-html="link.label"
            />
            <span
                v-else
                class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 bg-white cursor-default"
                v-html="link.label"
            />
        </template>
    </div>
</template>
