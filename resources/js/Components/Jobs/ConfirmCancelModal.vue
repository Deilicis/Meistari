<script setup lang="ts">
import { ref } from 'vue';

defineProps<{ show: boolean }>();
const emit  = defineEmits<{ close: []; submitted: [reason: string | null] }>();
const reason = ref('');

function submit() {
    emit('submitted', reason.value.trim() || null);
    reason.value = '';
}
function close() { reason.value = ''; emit('close'); }
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="close" />
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-base font-bold text-navy mb-1">Atcelt darbu?</h2>
                <p class="text-sm text-gray-500 mb-4">Šo darbību nevar atcelt.</p>

                <label class="block text-xs font-semibold text-gray-700 mb-1">Iemesls (neobligāts)</label>
                <textarea
                    v-model="reason"
                    rows="3"
                    placeholder="Paskaidro, kāpēc atcel..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none resize-none"
                />

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="close" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">Atpakaļ</button>
                    <button @click="submit" class="px-4 py-2 text-sm font-bold text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors">
                        Atcelt darbu
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
