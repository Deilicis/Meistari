<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { CurrencyEuroIcon } from '@heroicons/vue/24/outline';
import type { PriceProposal } from '@/types/proposal';
import { formatCurrency } from '@/utils/formatters';
import CounterProposalModal        from '@/Components/Jobs/Proposals/CounterProposalModal.vue';
import ConfirmAcceptProposalModal  from '@/Components/Jobs/Proposals/ConfirmAcceptProposalModal.vue';
import ConfirmRejectProposalModal  from '@/Components/Jobs/Proposals/ConfirmRejectProposalModal.vue';
import ConfirmWithdrawProposalModal from '@/Components/Jobs/Proposals/ConfirmWithdrawProposalModal.vue';

const props = defineProps<{
    proposal: PriceProposal;
    currentUserId: number;
    conversationId: number;
}>();

const emit = defineEmits<{
    proposalActed: [proposalId: number];
}>();

const { t } = useI18n();

const loading       = ref<string | null>(null);
const showAccept    = ref(false);
const showReject    = ref(false);
const showWithdraw  = ref(false);
const showCounter   = ref(false);

const isOwn     = computed(() => props.proposal.proposed_by.id === props.currentUserId);
const isPending = computed(() => props.proposal.is_pending);

const statusCls: Record<string, string> = {
    pending:   'bg-amber-100 text-amber-700',
    accepted:  'bg-emerald-100 text-emerald-700',
    countered: 'bg-blue-100 text-blue-700',
    rejected:  'bg-red-100 text-red-600',
    withdrawn: 'bg-gray-100 text-gray-500',
};

function formatMoney(amount: string): string {
    return formatCurrency(parseFloat(amount));
}

function formatRelative(iso: string): string {
    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1)   return t('chat.just_now');
    if (mins < 60)  return t('chat.minutes_ago', { n: mins });
    const hours = Math.floor(mins / 60);
    if (hours < 24) return t('notifications.ui.hours_ago', { n: hours });
    return new Date(iso).toLocaleDateString('lv-LV', { day: 'numeric', month: 'short' });
}

async function accept() {
    loading.value = 'accept';
    try {
        await axios.post(route('proposals.accept', props.proposal.id));
        toast.success(t('jobs.accept_proposal_success'));
        showAccept.value = false;
        emit('proposalActed', props.proposal.id);
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}

async function reject() {
    loading.value = 'reject';
    try {
        await axios.post(route('proposals.reject', props.proposal.id));
        toast.success(t('jobs.reject_proposal_success'));
        showReject.value = false;
        emit('proposalActed', props.proposal.id);
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}

async function withdraw() {
    loading.value = 'withdraw';
    try {
        await axios.post(route('proposals.withdraw', props.proposal.id));
        toast.success(t('jobs.withdraw_proposal_success'));
        showWithdraw.value = false;
        emit('proposalActed', props.proposal.id);
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}

async function counter(amount: number, note: string | null) {
    loading.value = 'counter';
    try {
        await axios.post(route('proposals.counter', props.proposal.id), { amount, note });
        toast.success(t('jobs.counter_proposal_success'));
        showCounter.value = false;
        emit('proposalActed', props.proposal.id);
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}
</script>

<template>
    <div class="w-full my-1">
        <!-- Card: full-width, distinct from text bubbles -->
        <div class="mx-auto max-w-sm border-l-4 border-navy bg-slate-50 rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-2 border-b border-slate-200 bg-white">
                <div class="flex items-center gap-2">
                    <CurrencyEuroIcon class="w-4 h-4 text-gold flex-shrink-0" />
                    <span class="text-xs font-bold text-navy uppercase tracking-wide">{{ t('proposals.title') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="text-xs font-semibold px-2 py-0.5 rounded-full"
                        :class="statusCls[proposal.status]"
                    >{{ proposal.status_label }}</span>
                    <a
                        v-if="proposal.job_request_id"
                        :href="`/jobs/${proposal.job_request_id}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-xs text-slate-400 hover:text-slate-600 hover:underline transition-colors whitespace-nowrap"
                    >{{ t('proposals.view_job') }}</a>
                </div>
            </div>

            <!-- Body -->
            <div class="px-4 py-3">
                <p class="text-2xl font-bold text-navy mb-1">{{ formatMoney(proposal.amount) }}</p>
                <p class="text-xs text-gray-500 mb-2">
                    {{ t('proposals.proposed_by') }} <span class="font-medium text-gray-700">{{ proposal.proposed_by.name }}</span>
                    <span class="ml-1.5 text-gray-400">{{ formatRelative(proposal.created_at) }}</span>
                </p>
                <p v-if="proposal.note" class="text-sm text-gray-600 italic leading-relaxed">"{{ proposal.note }}"</p>
            </div>

            <!-- Action footer (pending only) -->
            <div v-if="isPending" class="px-4 py-3 border-t border-slate-200 bg-white/60">
                <!-- Own proposal: only withdraw -->
                <div v-if="isOwn" class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ t('proposals.awaiting') }}</p>
                    <button
                        @click="showWithdraw = true"
                        :disabled="!!loading"
                        class="text-xs font-medium text-red-600 hover:text-red-700 disabled:opacity-50 transition-colors"
                    >
                        {{ t('proposals.withdraw_btn') }}
                    </button>
                </div>

                <!-- Recipient: accept / counter / reject -->
                <div v-else class="flex flex-wrap gap-2">
                    <button
                        @click="showAccept = true"
                        :disabled="!!loading"
                        class="flex-1 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg disabled:opacity-50 transition-colors"
                    >
                        {{ t('proposals.accept_btn', { price: formatMoney(proposal.amount) }) }}
                    </button>
                    <button
                        @click="showCounter = true"
                        :disabled="!!loading"
                        class="flex-1 px-3 py-1.5 text-xs font-semibold text-navy border border-navy/30 hover:bg-navy/5 rounded-lg disabled:opacity-50 transition-colors"
                    >
                        {{ t('proposals.counter_btn') }}
                    </button>
                    <button
                        @click="showReject = true"
                        :disabled="!!loading"
                        class="w-full text-xs font-medium text-red-600 hover:text-red-700 py-1 disabled:opacity-50 transition-colors"
                    >
                        {{ t('proposals.reject_btn') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <ConfirmAcceptProposalModal
        :show="showAccept"
        :proposal="proposal"
        @close="showAccept = false"
        @confirm="accept"
    />
    <ConfirmRejectProposalModal
        :show="showReject"
        @close="showReject = false"
        @confirm="reject"
    />
    <ConfirmWithdrawProposalModal
        :show="showWithdraw"
        :proposal="proposal"
        @close="showWithdraw = false"
        @confirm="withdraw"
    />
    <CounterProposalModal
        :show="showCounter"
        :current-proposal="proposal"
        @close="showCounter = false"
        @submit="counter"
    />
</template>
