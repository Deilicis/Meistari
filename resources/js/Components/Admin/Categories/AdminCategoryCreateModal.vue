<script setup lang="ts">
import { ref, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import type { AdminCategory } from '@/types/admin/category';

const props = defineProps<{
    show: boolean;
    parentCategory?: AdminCategory | null;
    topLevelCategories: AdminCategory[];
}>();

const emit = defineEmits<{
    close: [];
    submit: [data: { name: string; parent_id: number | null; icon: string | null }];
}>();

const name = ref('');
const parentId = ref<number | null>(props.parentCategory?.id ?? null);
const icon = ref('');

watch(() => props.show, (val) => {
    if (val) {
        name.value = '';
        parentId.value = props.parentCategory?.id ?? null;
        icon.value = '';
    }
});

watch(() => props.parentCategory, (cat) => {
    parentId.value = cat?.id ?? null;
});

function submit() {
    if (!name.value.trim()) return;
    emit('submit', {
        name:      name.value.trim(),
        parent_id: parentId.value,
        icon:      icon.value.trim() || null,
    });
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-navy">
                        {{ parentCategory ? `Pievienot apakškategoriju - ${parentCategory.name}` : 'Pievienot kategoriju' }}
                    </h2>
                    <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nosaukums <span class="text-red-500">*</span></label>
                        <input
                            v-model="name"
                            type="text"
                            maxlength="100"
                            required
                            autofocus
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                            placeholder="Kategorijas nosaukums"
                        />
                    </div>

                    <div v-if="!parentCategory">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vecākkategorija</label>
                        <select
                            v-model="parentId"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white"
                        >
                            <option :value="null">- Augstākā līmeņa kategorija -</option>
                            <option
                                v-for="cat in topLevelCategories.filter(c => !c.is_system)"
                                :key="cat.id"
                                :value="cat.id"
                            >{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ikona</label>
                        <input
                            v-model="icon"
                            type="text"
                            maxlength="100"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                            placeholder="piem. WrenchScrewdriverIcon"
                        />
                        <p class="text-xs text-gray-400 mt-1">Heroicon komponenta nosaukums.</p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="emit('close')"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                        >Atcelt</button>
                        <button
                            type="submit"
                            :disabled="!name.trim()"
                            class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-navy hover:bg-navy/90 rounded-xl transition-colors disabled:opacity-40"
                        >Pievienot</button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
