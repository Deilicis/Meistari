<script setup lang="ts">
import { ref, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import type { AdminCategory } from '@/types/admin/category';

const props = defineProps<{
    show: boolean;
    category: AdminCategory | null;
    topLevelCategories: AdminCategory[];
}>();

const emit = defineEmits<{
    close: [];
    submit: [data: { name: string; icon: string | null; parent_id: number | null; regenerate_slug: boolean }];
}>();

const name = ref('');
const icon = ref('');
const parentId = ref<number | null>(null);
const regenerateSlug = ref(false);

watch(() => props.show, (val) => {
    if (val && props.category) {
        name.value = props.category.name;
        icon.value = props.category.icon ?? '';
        parentId.value = props.category.parent_id;
        regenerateSlug.value = false;
    }
});

function submit() {
    if (!name.value.trim()) return;
    emit('submit', {
        name:            name.value.trim(),
        icon:            icon.value.trim() || null,
        parent_id:       parentId.value,
        regenerate_slug: regenerateSlug.value,
    });
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show && category" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-navy">Rediģēt - {{ category.name }}</h2>
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
                        />
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
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Vecākkategorija
                            <span v-if="category.is_system" class="ml-1 text-xs text-gray-400">(bloķēts - sistēmas kategorija)</span>
                        </label>
                        <select
                            v-model="parentId"
                            :disabled="category.is_system"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white disabled:bg-gray-50 disabled:text-gray-400"
                        >
                            <option :value="null">- Augstākā līmeņa kategorija -</option>
                            <option
                                v-for="cat in topLevelCategories.filter(c => !c.is_system && c.id !== category!.id)"
                                :key="cat.id"
                                :value="cat.id"
                            >{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="regenerateSlug"
                                type="checkbox"
                                :disabled="category.is_system"
                                class="rounded border-gray-300 text-navy focus:ring-navy disabled:opacity-40"
                            />
                            <span class="text-sm text-gray-700">
                                Ģenerēt slug no nosaukuma
                                <span v-if="category.is_system" class="text-xs text-gray-400">(bloķēts)</span>
                            </span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1">Pašreizējais slug: <code class="bg-gray-50 px-1 rounded">{{ category.slug }}</code></p>
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
                        >Saglabāt</button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
