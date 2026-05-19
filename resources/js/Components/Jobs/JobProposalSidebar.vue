<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { CurrencyEuroIcon, ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatters';
import type { ProposalState, PriceProposal } from '@/types/proposal';
import ConfirmAcceptProposalModal  from './Proposals/ConfirmAcceptProposalModal.vue';
import ConfirmRejectProposalModal  from './Proposals/ConfirmRejectProposalModal.vue';
import ConfirmWithdrawProposalModal from './Proposals/ConfirmWithdrawProposalModal.vue';
import CounterProposalModal        from './Proposals/CounterProposalModal.vue';
import SubmitFreshProposalModal    from './Proposals/SubmitFreshProposalModal.vue';

const props = defineProps<{
    proposals: ProposalState;
    jobId: number;
    currentUserId: number;
}>();

const { t } = useI18n();

const loading = ref<string | null>(null);
const showHistory = ref(false);
const showAcceptModal = ref(false);
const showRejectModal = ref(false);
const showWithdrawModal = ref(false);
const showCounterModal = ref(false);
const showFreshModal = ref(false);

const pending = computed(() => props.proposals.pending);

const isOwn = computed(() =>
    pending.value !== null && pending.value.proposed_by.id === props.currentUserId,
);

function formatMoney(amount: string | number | null): string {
    if (amount === null || amount === undefined) return '-';
    const n = typeof amount === 'string' ? parseFloat(amount) : amount;
    if (isNaN(n)) return '-';
    return formatCurrency(n);
}

function formatRelative(iso: string | null): string {
    if (!iso) return '';
    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1)   return t('chat.just_now');
    if (mins < 60)  return t('chat.minutes_ago', { n: mins });
    const hours = Math.floor(mins / 60);
    if (hours < 24) return t('notifications.ui.hours_ago', { n: hours });
    return new Date(iso).toLocaleDateString('lv-LV', { day: 'numeric', month: 'short' });
}

const historyStatusCls: Record<string, string> = {
    pending:   'bg-amber-100 text-amber-700',
    accepted:  'bg-emerald-100 text-emerald-700',
    countered: 'bg-blue-100 text-blue-700',
    rejected:  'bg-red-100 text-red-600',
    withdrawn: 'bg-gray-100 text-gray-500',
};

async function accept() {
    if (!pending.value) return;
    loading.value = 'accept';
    try {
        await axios.post(route('proposals.accept', pending.value.id));
        toast.success(t('jobs.accept_proposal_success'));
        showAcceptModal.value = false;
        router.reload({ only: ['job', 'proposals', 'allowed_actions', 'applications'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}

async function reject() {
    if (!pending.value) return;
    loading.value = 'reject';
    try {
        await axios.post(route('proposals.reject', pending.value.id));
        toast.success(t('jobs.reject_proposal_success'));
        showRejectModal.value = false;
        router.reload({ only: ['proposals', 'applications'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}

async function withdraw() {
    if (!pending.value) return;
    loading.value = 'withdraw';
    try {
        await axios.post(route('proposals.withdraw', pending.value.id));
        toast.success(t('jobs.withdraw_proposal_success'));
        showWithdrawModal.value = false;
        router.reload({ only: ['proposals', 'applications'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}

async function counter(amount: number, note: string | null) {
    if (!pending.value) return;
    loading.value = 'counter';
    try {
        await axios.post(route('proposals.counter', pending.value.id), { amount, note });
        toast.success(t('jobs.counter_proposal_success'));
        showCounterModal.value = false;
        router.reload({ only: ['proposals', 'applications'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}

async function submitFresh(amount: number, note: string | null) {
    loading.value = 'fresh';
    try {
        await axios.post(route('proposals.store', props.proposals.application_id), { amount, note });
        toast.success(t('jobs.fresh_proposal_success'));
        showFreshModal.value = false;
        router.reload({ only: ['proposals', 'applications'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('proposals.error_toast'));
    } finally {
        loading.value = null;
    }
}

function historyLabel(p: PriceProposal): string {
    return p.status_label;
}
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-4">
            <CurrencyEuroIcon class="w-4 h-4 text-gold" />
            <h2 class="text-sm font-bold text-navy">{{ t('proposals.title') }}</h2>
        </div>

        <!-- Pending proposal exists -->
        <template v-if="pending">
            <!-- Accepted state -->
            <div v-if="pending.status === 'accepted'" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-1">{{ t('proposals.accepted_header') }}</p>
                <p class="text-2xl font-bold text-emerald-700">{{ formatMoney(pending.amount) }}</p>
            </div>

            <!-- Active pending proposal -->
            <template v-else>
                <div class="mb-4">
                    <p class="text-2xl font-bold text-navy mb-1">{{ formatMoney(pending.amount) }}</p>
                    <p class="text-xs text-gray-500">
                        <span>{{ t('proposals.proposed_by') }} {{ pending.proposed_by.name }}</span>
                        <span class="ml-2 text-gray-400">{{ formatRelative(pending.created_at) }}</span>
                    </p>
                    <p v-if="pending.note" class="mt-2 text-sm text-gray-600 italic leading-relaxed">"{{ pending.note }}"</p>
                    <span class="inline-block mt-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                        {{ t('proposals.awaiting_badge') }}
                    </span>
                </div>

                <!-- Own proposal: only withdraw -->
                <div v-if="isOwn" class="space-y-2">
                    <p class="text-xs text-gray-500 font-medium">{{ t('proposals.own_awaiting') }}</p>
                    <button
                        v-if="proposals.can_withdraw"
                        @click="showWithdrawModal = true"
                        :disabled="!!loading"
                        class="text-sm font-medium text-red-600 hover:text-red-700 disabled:opacity-50 transition-colors"
                    >
                        {{ t('proposals.withdraw_btn') }}
                    </button>
                </div>

                <!-- Recipient: accept / counter / reject -->
                <div v-else class="flex flex-col gap-2">
                    <button
                        v-if="proposals.can_accept"
                        @click="showAcceptModal = true"
                        :disabled="!!loading"
                        class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl disabled:opacity-50 transition-colors"
                    >
                        {{ loading === 'accept' ? t('proposals.processing') : t('proposals.accept_btn', { price: formatMoney(pending.amount) }) }}
                    </button>
                    <button
                        v-if="proposals.can_counter"
                        @click="showCounterModal = true"
                        :disabled="!!loading"
                        class="w-full px-4 py-2.5 text-sm font-semibold text-navy bg-white border border-navy/30 hover:bg-navy/5 rounded-xl disabled:opacity-50 transition-colors"
                    >
                        {{ t('proposals.counter_btn') }}
                    </button>
                    <button
                        v-if="proposals.can_reject"
                        @click="showRejectModal = true"
                        :disabled="!!loading"
                        class="text-sm font-medium text-red-600 hover:text-red-700 py-1 disabled:opacity-50 transition-colors"
                    >
                        {{ t('proposals.reject_btn') }}
                    </button>
                </div>
            </template>
        </template>

        <!-- No pending proposal -->
        <template v-else>
            <div class="text-center py-4">
                <p v-if="proposals.history.length > 0" class="text-sm text-gray-500 mb-3">
                    {{ t('proposals.no_active') }}
                </p>
                <p v-else class="text-sm text-gray-500 mb-3">
                    {{ t('proposals.no_proposals') }}
                </p>
                <button
                    v-if="proposals.can_submit_fresh"
                    @click="showFreshModal = true"
                    :disabled="!!loading"
                    class="px-4 py-2 text-sm font-semibold text-navy bg-white border border-navy/30 hover:bg-navy/5 rounded-xl disabled:opacity-50 transition-colors"
                >
                    {{ t('proposals.add_proposal') }}
                </button>
            </div>
        </template>

        <!-- History accordion -->
        <div v-if="proposals.history.length > 0" class="mt-4 border-t border-gray-100 pt-3">
            <button
                @click="showHistory = !showHistory"
                class="flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-navy transition-colors w-full text-left"
            >
                <component :is="showHistory ? ChevronUpIcon : ChevronDownIcon" class="w-3.5 h-3.5" />
                {{ t('proposals.history_header', { count: proposals.history.length }) }}
            </button>

            <div v-if="showHistory" class="mt-3 space-y-2">
                <div
                    v-for="p in proposals.history"
                    :key="p.id"
                    class="flex items-start justify-between gap-2 text-xs py-1.5 border-b border-gray-50 last:border-0"
                >
                    <div class="min-w-0">
                        <span class="font-semibold text-navy">{{ formatMoney(p.amount) }}</span>
                        <span class="text-gray-500 ml-1.5">- {{ p.proposed_by.name }}</span>
                        <p v-if="p.note" class="text-gray-400 italic mt-0.5 line-clamp-1">"{{ p.note }}"</p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <span class="px-1.5 py-0.5 rounded-full font-semibold" :class="historyStatusCls[p.status]">
                            {{ historyLabel(p) }}
                        </span>
                        <span class="text-gray-400">{{ formatRelative(p.created_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <ConfirmAcceptProposalModal
        :show="showAcceptModal"
        :proposal="pending"
        @close="showAcceptModal = false"
        @confirm="accept"
    />
    <ConfirmRejectProposalModal
        :show="showRejectModal"
        @close="showRejectModal = false"
        @confirm="reject"
    />
    <ConfirmWithdrawProposalModal
        :show="showWithdrawModal"
        :proposal="pending"
        @close="showWithdrawModal = false"
        @confirm="withdraw"
    />
    <CounterProposalModal
        :show="showCounterModal"
        :current-proposal="pending"
        @close="showCounterModal = false"
        @submit="counter"
    />
    <SubmitFreshProposalModal
        :show="showFreshModal"
        @close="showFreshModal = false"
        @submit="submitFresh"
    />
</template>
