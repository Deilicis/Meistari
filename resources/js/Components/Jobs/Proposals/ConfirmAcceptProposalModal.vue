<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Modal from '@/Components/Common/Modal.vue';
import { formatCurrency } from '@/utils/formatters';
import type { PriceProposal } from '@/types/proposal';

const { t } = useI18n();

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
    return formatCurrency(parseFloat(amount));
}
</script>

<template>
    <Modal :show="show" @close="emit('close')" maxWidth="sm">
        <div v-if="proposal" class="p-6">
            <h3 class="text-base font-bold text-navy mb-3">{{ t('proposals.accept_confirm_title') }}</h3>
            <p class="text-sm text-gray-600 mb-5">
                {{ t('proposals.accept_confirm_desc', { amount: formatMoney(proposal.amount) }) }}
            </p>
            <div class="flex justify-end gap-3">
                <button
                    @click="emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    {{ t('common.cancel') }}
                </button>
                <button
                    @click="emit('confirm')"
                    class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors"
                >
                    {{ t('proposals.accept_confirm_btn') }}
                </button>
            </div>
        </div>
    </Modal>
</template>
