<script setup lang="ts">
import { computed } from 'vue';
import { XMarkIcon, TrashIcon } from '@heroicons/vue/24/outline';
import type { AdminCategory } from '@/types/admin/category';

const props = defineProps<{
    show: boolean;
    category: AdminCategory | null;
}>();

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

const blockingReasons = computed(() => {
    if (!props.category) return [];
    const reasons: string[] = [];
    if (props.category.is_system)           reasons.push('Sistēmas kategorija');
    if (props.category.services_count > 0)  reasons.push(`${props.category.services_count} aktīvi pakalpojumi`);
    if (props.category.job_requests_count > 0) reasons.push(`${props.category.job_requests_count} aktīvi darbi`);
    if (props.category.children_count > 0)  reasons.push(`${props.category.children_count} apakškategorijas`);
    return reasons;
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show && category" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-navy">Dzēst kategoriju</h2>
                    <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-700">
                        Vai tiešām dzēst kategoriju <span class="font-semibold">"{{ category.name }}"</span>?
                    </p>

                    <div v-if="blockingReasons.length > 0" class="p-3 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm font-semibold text-red-700 mb-2">Šo kategoriju nevar dzēst, jo:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li v-for="reason in blockingReasons" :key="reason" class="text-sm text-red-600">{{ reason }}</li>
                        </ul>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="emit('close')"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                        >Atcelt</button>
                        <button
                            type="button"
                            @click="emit('confirm')"
                            :disabled="!category.can_delete"
                            class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            <TrashIcon class="w-4 h-4" />
                            Dzēst
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
