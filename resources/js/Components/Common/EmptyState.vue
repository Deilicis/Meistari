<script setup lang="ts">
defineProps<{ title: string; description?: string }>();
</script>

<template>
    <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-navy/5 flex items-center justify-center mb-4">
            <slot name="icon" />
        </div>
        <h3 class="text-base font-semibold text-gray-900 mb-1">{{ title }}</h3>
        <p v-if="description" class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">{{ description }}</p>
        <slot name="action" />
    </div>
</template>
