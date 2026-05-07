<script setup lang="ts">
import { computed } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/solid';
import type { JobStatus } from '@/types/jobLifecycle';

const props = defineProps<{ job: { status: JobStatus }; isClient: boolean }>();

type Step = { key: JobStatus; label: string };

const mainSteps: Step[] = [
    { key: 'open',                  label: 'Atvērts' },
    { key: 'accepted',              label: 'Pieņemts' },
    { key: 'in_progress',           label: 'Darbā' },
    { key: 'awaiting_confirmation', label: 'Gaida' },
    { key: 'completed',             label: 'Pabeigts' },
];

const statusOrder: Record<JobStatus, number> = {
    open: 0, accepted: 1, in_progress: 2, awaiting_confirmation: 3,
    completed: 4, disputed: 3, cancelled: -1,
};

const currentIndex = computed(() => statusOrder[props.job.status] ?? -1);
const accentColor  = computed(() => props.isClient ? 'bg-emerald-500' : 'bg-gold');
const textAccent   = computed(() => props.isClient ? 'text-emerald-600' : 'text-yellow-600');

function stepState(step: Step, i: number): 'done' | 'active' | 'future' {
    const stepIdx = i;
    const curr    = currentIndex.value;
    if (props.job.status === 'cancelled' || props.job.status === 'disputed') {
        return stepIdx < curr ? 'done' : 'future';
    }
    if (stepIdx < curr) return 'done';
    if (stepIdx === curr) return 'active';
    return 'future';
}
</script>

<template>
    <div class="w-full">
        <!-- Normal flow -->
        <div v-if="job.status !== 'cancelled' && job.status !== 'disputed'" class="flex items-center gap-0">
            <template v-for="(step, i) in mainSteps" :key="step.key">
                <!-- Step node -->
                <div class="flex flex-col items-center min-w-0">
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 transition-colors"
                        :class="{
                            [accentColor + ' text-white']: stepState(step, i) === 'active',
                            'bg-green-500 text-white': stepState(step, i) === 'done',
                            'bg-gray-100 text-gray-400': stepState(step, i) === 'future',
                        }"
                    >
                        <CheckIcon v-if="stepState(step, i) === 'done'" class="w-4 h-4" />
                        <span v-else>{{ i + 1 }}</span>
                    </div>
                    <p
                        class="text-[10px] mt-1 text-center leading-tight hidden sm:block"
                        :class="{
                            [textAccent]: stepState(step, i) === 'active',
                            'text-green-600 font-medium': stepState(step, i) === 'done',
                            'text-gray-400': stepState(step, i) === 'future',
                        }"
                    >
                        {{ step.label }}
                    </p>
                </div>
                <!-- Connector -->
                <div
                    v-if="i < mainSteps.length - 1"
                    class="h-0.5 flex-1 mx-1 mb-4 rounded transition-colors"
                    :class="i < currentIndex ? 'bg-green-400' : 'bg-gray-200'"
                />
            </template>
        </div>

        <!-- Disputed branch -->
        <div v-else-if="job.status === 'disputed'" class="flex items-center gap-3 py-2 px-4 bg-red-50 rounded-xl border border-red-200">
            <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center shrink-0">
                <span class="text-white text-xs font-bold">!</span>
            </div>
            <div>
                <p class="text-sm font-bold text-red-700">Strīdā</p>
                <p class="text-xs text-red-500">Mēs izskatām situāciju un sazināsimies ar jums.</p>
            </div>
        </div>

        <!-- Cancelled -->
        <div v-else class="flex items-center gap-3 py-2 px-4 bg-gray-50 rounded-xl border border-gray-200">
            <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center shrink-0">
                <span class="text-white text-xs font-bold">✕</span>
            </div>
            <p class="text-sm font-medium text-gray-500">Darbs tika atcelts</p>
        </div>
    </div>
</template>
