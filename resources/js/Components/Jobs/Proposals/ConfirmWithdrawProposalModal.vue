<script setup lang="ts">
import Modal from '@/Components/Common/Modal.vue';
import type { PriceProposal } from '@/types/proposal';

const props = defineProps<{
    show: boolean;
    proposal: PriceProposal | null;
}>();

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

function formatMoney(amount: string | null): string {
    if (!amount) return '—';
    return new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', minimumFractionDigits: 2 }).format(parseFloat(amount));
}
</script>

<template>
    <Modal :show="show" @close="emit('close')" maxWidth="sm">
        <div v-if="proposal" class="p-6">
            <h3 class="text-base font-bold text-navy mb-3">Atsaukt piedāvājumu</h3>
            <p class="text-sm text-gray-600 mb-5">
                Vai tiešām vēlies atsaukt savu piedāvājumu <span class="font-semibold text-navy">{{ formatMoney(proposal.amount) }}</span>?
            </p>
            <div class="flex justify-end gap-3">
                <button
                    @click="emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Atcelt
                </button>
                <button
                    @click="emit('confirm')"
                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
                >
                    Atsaukt
                </button>
            </div>
        </div>
    </Modal>
</template>
