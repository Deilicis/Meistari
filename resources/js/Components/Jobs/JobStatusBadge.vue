<script setup lang="ts">
import type { JobStatus } from '@/types/jobLifecycle';

defineProps<{ status: JobStatus; label: string }>();

const colorMap: Record<JobStatus, string> = {
    open:                  'bg-slate-100 text-slate-700 ring-slate-200',
    accepted:              'bg-blue-100 text-blue-700 ring-blue-200',
    in_progress:           'bg-yellow-100 text-yellow-700 ring-yellow-200',
    awaiting_confirmation: 'bg-orange-100 text-orange-700 ring-orange-200',
    completed:             'bg-green-100 text-green-700 ring-green-200',
    disputed:              'bg-red-100 text-red-700 ring-red-200',
    cancelled:             'bg-gray-100 text-gray-500 ring-gray-200',
};
</script>

<template>
    <span
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1"
        :class="colorMap[status]"
    >
        {{ label }}
    </span>
</template>
