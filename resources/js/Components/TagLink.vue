<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href?: string;
}>();
</script>

<template>
    <Link :href="href || '#'" class="bg-white border border-gray-200 hover:border-navy hover:text-navy px-4 py-1.5 rounded-full transition-colors text-sm font-medium shadow-sm hover:shadow">
        <slot />
    </Link>
</template>