<script setup lang="ts">
import { ref } from 'vue';
import Modal from '@/Components/Common/Modal.vue';

defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    submit: [amount: number, note: string | null];
}>();

const amount = ref<string>('');
const note   = ref<string>('');
const error  = ref<string | null>(null);

function handleSubmit() {
    const n = parseFloat(amount.value);
    if (!amount.value || isNaN(n) || n < 1) {
        error.value = 'Ievadiet derīgu summu (min €1).';
        return;
    }
    error.value = null;
    emit('submit', n, note.value.trim() || null);
    amount.value = '';
    note.value   = '';
}

function handleClose() {
    amount.value = '';
    note.value   = '';
    error.value  = null;
    emit('close');
}
</script>

<template>
    <Modal :show="show" @close="handleClose" maxWidth="sm">
        <div class="p-6">
            <h3 class="text-base font-bold text-navy mb-4">Jauns cenas piedāvājums</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cena (€) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">€</span>
                        <input
                            v-model="amount"
                            type="number"
                            min="1"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy"
                        />
                    </div>
                    <p v-if="error" class="text-xs text-red-600 mt-1">{{ error }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Piezīme <span class="text-gray-400">(neobligāti)</span></label>
                    <textarea
                        v-model="note"
                        rows="3"
                        maxlength="500"
                        placeholder="Paskaidrojiet savu piedāvājumu..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/30 focus:border-navy resize-none"
                    />
                    <p class="text-xs text-gray-400 text-right mt-0.5">{{ note.length }}/500</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-5">
                <button
                    @click="handleClose"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Atcelt
                </button>
                <button
                    @click="handleSubmit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-navy hover:bg-navy/90 rounded-lg transition-colors"
                >
                    Nosūtīt piedāvājumu
                </button>
            </div>
        </div>
    </Modal>
</template>
