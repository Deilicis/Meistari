<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href?: string;
}>();
</script>

<template>
    <Link :href="href || '#'" class="text-gray-500 hover:text-navy transition-colors font-medium">
        <slot />
    </Link>
</template>