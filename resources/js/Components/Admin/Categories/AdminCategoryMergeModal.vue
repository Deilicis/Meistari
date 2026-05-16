<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import type { AdminCategory } from '@/types/admin/category';

const props = defineProps<{
    show: boolean;
    source: AdminCategory | null;
    allCategories: AdminCategory[];
}>();

const emit = defineEmits<{
    close: [];
    submit: [data: { source_id: number; target_id: number }];
}>();

const targetId = ref<number | null>(null);

watch(() => props.show, (val) => {
    if (val) targetId.value = null;
});

const targetOptions = computed(() => {
    if (!props.source) return [];
    const isTopLevel = props.source.parent_id === null;

    const pool = isTopLevel
        ? props.allCategories
        : props.allCategories.flatMap(c => c.children ?? []);

    return pool.filter(c =>
        c.id !== props.source!.id &&
        !c.is_system &&
        (isTopLevel ? c.parent_id === null : c.parent_id !== null)
    );
});

const targetName = computed(() => targetOptions.value.find(c => c.id === targetId.value)?.name ?? '');

function submit() {
    if (!props.source || !targetId.value) return;
    emit('submit', { source_id: props.source.id, target_id: targetId.value });
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show && source" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-navy">Apvienot - {{ source.name }}</h2>
                    <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apvienot ar kategoriju <span class="text-red-500">*</span></label>
                        <select
                            v-model="targetId"
                            required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white"
                        >
                            <option :value="null" disabled>- Izvēlēties mērķa kategoriju -</option>
                            <option v-for="cat in targetOptions" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <p v-if="targetOptions.length === 0" class="text-xs text-amber-600 mt-1">Nav pieejamu mērķa kategoriju.</p>
                    </div>

                    <div v-if="targetId" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                        Visi pakalpojumi un darbi tiks pārvietoti uz
                        <span class="font-semibold">„{{ targetName }}"</span>,
                        un <span class="font-semibold">„{{ source.name }}"</span> tiks dzēsta.
                        <span v-if="source.children_count > 0"> Apakškategorijas tiks pārvietotas uz „{{ targetName }}".</span>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="emit('close')"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                        >Atcelt</button>
                        <button
                            type="submit"
                            :disabled="!targetId"
                            class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-colors disabled:opacity-40"
                        >Apvienot</button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
