<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import { formatDate, formatCurrency } from '@/utils/formatters';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import JobStatusBadge from '@/Components/Jobs/JobStatusBadge.vue';
import JobLifecycleTimeline from '@/Components/Jobs/JobLifecycleTimeline.vue';
import ConfirmDisputeModal from '@/Components/Jobs/ConfirmDisputeModal.vue';
import ConfirmCancelModal from '@/Components/Jobs/ConfirmCancelModal.vue';
import JobProposalSidebar from '@/Components/Jobs/JobProposalSidebar.vue';
import CounterProposalModal from '@/Components/Jobs/Proposals/CounterProposalModal.vue';
import SubmitFreshProposalModal from '@/Components/Jobs/Proposals/SubmitFreshProposalModal.vue';
import ConfirmAcceptProposalModal from '@/Components/Jobs/Proposals/ConfirmAcceptProposalModal.vue';
import ConfirmRejectProposalModal from '@/Components/Jobs/Proposals/ConfirmRejectProposalModal.vue';
import ConfirmWithdrawProposalModal from '@/Components/Jobs/Proposals/ConfirmWithdrawProposalModal.vue';
import {
    ArrowLeftIcon,
    UserIcon,
    CurrencyEuroIcon,
    ClockIcon,
    MapPinIcon,
    CalendarIcon,
    ShieldExclamationIcon,
    ClipboardDocumentListIcon,
    ChatBubbleLeftRightIcon,
    StarIcon,
    BanknotesIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';
import type { JobStatus } from '@/types/jobLifecycle';
import type { ProposalState, PriceProposal } from '@/types/proposal';

// ─── Types ────────────────────────────────────────────────────────────────────

type ViewerRole = 'owner' | 'applicant' | 'accepted_master' | 'admin';
type AppStatus = 'pending' | 'shortlisted' | 'accepted' | 'rejected' | 'completed' | 'cancelled';
type PageAction =
    | 'shortlist_application' | 'accept_application' | 'reject_application'
    | 'edit_job' | 'delete_job' | 'cancel' | 'pay'
    | 'confirm_complete' | 'dispute' | 'chat_with_master'
    | 'mark_complete' | 'chat_with_seeker'
    | 'withdraw_application'
    | 'resolve_dispute_release' | 'resolve_dispute_refund';

interface AppProfile {
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    type: 'individual' | 'company' | null;
    city: string | null;
    avatar: string | null;
}

interface PageApplication {
    id: number;
    cover_letter: string | null;
    price_offer: string | null;
    status: AppStatus;
    created_at: string;
    pending_proposal: PriceProposal | null;
    user: { id: number; name: string; profile: AppProfile | null };
}

interface JobData {
    id: number;
    title: string;
    description: string;
    status: JobStatus;
    status_label: string;
    budget: string | null;
    agreed_price: string | null;
    price_type: 'fixed' | 'hourly' | null;
    deadline: string | null;
    location: string[];
    images: string[];
    created_at: string | null;
    master_completed_at: string | null;
    completed_at: string | null;
    client: { id: number; name: string };
    master: { id: number; name: string } | null;
    escrow: { status: string; amount: string; held_at: string | null; auto_release_at: string | null } | null;
    pending_category_suggestion?: { id: number; name: string; status: string; review_note: string | null } | null;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    job: JobData;
    viewer_role: ViewerRole;
    applications: PageApplication[] | null;
    own_application: PageApplication | null;
    allowed_actions: PageAction[];
    chat: { other_user_id: number; conversation_id: number | null } | null;
    proposals: ProposalState | null;
}>();

// ─── Auth ─────────────────────────────────────────────────────────────────────

const { t } = useI18n();

const page = usePage<{ auth: { user: { id: number } } }>();
const currentUserId = computed(() => page.props.auth.user.id);

// ─── Reactive state ───────────────────────────────────────────────────────────

const job     = ref<JobData>(props.job);
const apps    = ref<PageApplication[]>(props.applications ?? []);
const ownApp  = ref<PageApplication | null>(props.own_application);
const actions = ref<PageAction[]>(props.allowed_actions);

watch(() => props.job,              v => { job.value     = v; });
watch(() => props.applications,     v => { apps.value    = v ?? []; });
watch(() => props.own_application,  v => { ownApp.value  = v; });
watch(() => props.allowed_actions,  v => { actions.value = v; });

const loading          = ref<string | null>(null);
const showDisputeModal = ref(false);
const showCancelModal  = ref(false);

// Per-application proposal modal state
const counteringApp      = ref<PageApplication | null>(null);
const freshProposalApp   = ref<PageApplication | null>(null);
const acceptingProposal  = ref<PriceProposal | null>(null);
const rejectingProposal  = ref<PriceProposal | null>(null);
const withdrawingProposal = ref<PriceProposal | null>(null);

// ─── Helpers ──────────────────────────────────────────────────────────────────

const has = (action: PageAction) => actions.value.includes(action);

const showAppsSection = computed(
    () => props.applications !== null && (props.viewer_role === 'owner' || props.viewer_role === 'admin'),
);

const showActionPanel = computed(() => {
    const panelActions: PageAction[] = [
        'pay', 'mark_complete', 'confirm_complete', 'dispute', 'cancel',
        'withdraw_application', 'delete_job', 'resolve_dispute_release', 'resolve_dispute_refund',
    ];
    return actions.value.some(a => panelActions.includes(a));
});

const showChat = computed(() => has('chat_with_master') || has('chat_with_seeker'));

const backHref = computed(() => {
    switch (props.viewer_role) {
        case 'owner':           return route('seeker.job-requests.index');
        case 'accepted_master':
        case 'applicant':       return route('master.applications.index');
        case 'admin':           return route('admin.job-requests.index');
    }
});

const backLabel = computed(() => {
    switch (props.viewer_role) {
        case 'owner':           return t('jobs.back_my_jobs');
        case 'accepted_master':
        case 'applicant':       return t('jobs.back_my_applications');
        case 'admin':           return t('jobs.back_admin_jobs');
    }
});

function applicantName(app: PageApplication): string {
    const p = app.user.profile;
    if (!p) return app.user.name;
    if (p.type === 'company') return p.company_name ?? app.user.name;
    const parts = [p.first_name, p.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : app.user.name;
}

function formatMoney(amount: string | number | null | undefined, type?: string | null): string {
    if (amount === null || amount === undefined || amount === '') return '-';
    const n = typeof amount === 'string' ? parseFloat(amount) : amount;
    if (isNaN(n)) return '-';
    const formatted = formatCurrency(n);
    return type === 'hourly' ? `${formatted}/h` : formatted;
}

const appStatusBadgeClass: Record<AppStatus, string> = {
    pending:     'bg-amber-100 text-amber-700',
    shortlisted: 'bg-blue-100 text-blue-700',
    accepted:    'bg-emerald-100 text-emerald-700',
    rejected:    'bg-gray-100 text-gray-500',
    completed:   'bg-green-100 text-green-700',
    cancelled:   'bg-red-100 text-red-600',
};

// ─── Lifecycle actions ────────────────────────────────────────────────────────

async function postLifecycle(action: string, body: Record<string, unknown> = {}) {
    if (loading.value) return;
    loading.value = action;
    try {
        const urlMap: Record<string, string> = {
            pay:             route('jobs.lifecycle.pay',          job.value.id),
            mark_complete:   route('jobs.lifecycle.mark-complete', job.value.id),
            confirm_complete:route('jobs.lifecycle.confirm',      job.value.id),
            dispute:         route('jobs.lifecycle.dispute',      job.value.id),
            cancel:          route('jobs.lifecycle.cancel',       job.value.id),
        };
        const url = urlMap[action];
        if (!url) { loading.value = null; return; }
        const { data } = await axios.post(url, body);
        if (action === 'pay' && data.url) {
            window.location.href = data.url;
            return;
        }
        toast.success(t('jobs.shortlist_success'));
        router.reload({ only: ['job', 'allowed_actions', 'chat'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
    } finally {
        if (loading.value === action) loading.value = null;
    }
}

function handleDisputeSubmit(reason: string) {
    showDisputeModal.value = false;
    postLifecycle('dispute', { reason });
}

function handleCancelSubmit(reason: string | null) {
    showCancelModal.value = false;
    postLifecycle('cancel', { reason: reason ?? undefined });
}

async function handleDelete() {
    if (!confirm(t('jobs.delete_confirm_dialog'))) return;
    loading.value = 'delete_job';
    try {
        await axios.delete(route('api.job-requests.destroy', job.value.id));
        toast.success(t('jobs.delete_success_toast'));
        router.visit(route('seeker.job-requests.index'));
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
        loading.value = null;
    }
}

// ─── Application actions (owner) ─────────────────────────────────────────────

async function handleShortlist(app: PageApplication) {
    loading.value = `shortlist-${app.id}`;
    try {
        await axios.patch(route('api.applications.shortlist', app.id));
        toast.success(t('jobs.shortlist_success'));
        router.reload({ only: ['applications', 'job', 'allowed_actions'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
    } finally {
        loading.value = null;
    }
}

async function handleAcceptProposal(proposal: PriceProposal) {
    loading.value = `accept-proposal-${proposal.id}`;
    try {
        await axios.post(route('proposals.accept', proposal.id));
        toast.success(t('jobs.accept_proposal_success'));
        acceptingProposal.value = null;
        router.reload({ only: ['job', 'applications', 'allowed_actions', 'chat', 'proposals'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
    } finally {
        loading.value = null;
    }
}

async function handleRejectProposal(proposal: PriceProposal) {
    loading.value = `reject-proposal-${proposal.id}`;
    try {
        await axios.post(route('proposals.reject', proposal.id));
        toast.success(t('jobs.reject_proposal_success'));
        rejectingProposal.value = null;
        router.reload({ only: ['applications', 'allowed_actions', 'proposals'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
    } finally {
        loading.value = null;
    }
}

async function handleWithdrawProposal(proposal: PriceProposal) {
    loading.value = `withdraw-proposal-${proposal.id}`;
    try {
        await axios.post(route('proposals.withdraw', proposal.id));
        toast.success(t('jobs.withdraw_proposal_success'));
        withdrawingProposal.value = null;
        router.reload({ only: ['applications', 'proposals'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
    } finally {
        loading.value = null;
    }
}

async function handleCounterProposal(proposal: PriceProposal, amount: number, note: string | null) {
    loading.value = `counter-proposal-${proposal.id}`;
    try {
        await axios.post(route('proposals.counter', proposal.id), { amount, note });
        toast.success(t('jobs.counter_proposal_success'));
        counteringApp.value = null;
        router.reload({ only: ['applications', 'proposals'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
    } finally {
        loading.value = null;
    }
}

async function handleFreshProposal(app: PageApplication, amount: number, note: string | null) {
    loading.value = `fresh-proposal-${app.id}`;
    try {
        await axios.post(route('proposals.store', app.id), { amount, note });
        toast.success(t('jobs.fresh_proposal_success'));
        freshProposalApp.value = null;
        router.reload({ only: ['applications', 'proposals'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
    } finally {
        loading.value = null;
    }
}

// ─── Own application (applicant / master) ────────────────────────────────────

async function handleWithdraw() {
    if (!ownApp.value) return;
    loading.value = 'withdraw';
    try {
        await axios.delete(route('api.applications.destroy', ownApp.value.id));
        toast.success(t('jobs.withdraw_app_success'));
        router.visit(route('master.applications.index'));
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.delete_failed'));
    } finally {
        loading.value = null;
    }
}

// ─── Chat ─────────────────────────────────────────────────────────────────────

async function handleChat() {
    if (!props.chat) return;
    if (props.chat.conversation_id) {
        router.visit(route('chat.show', props.chat.conversation_id));
        return;
    }
    loading.value = 'chat';
    try {
        const { data } = await axios.post(route('chat.start'), { receiver_id: props.chat.other_user_id });
        router.visit(route('chat.show', data.conversation_id));
    } catch (e: any) {
        toast.error(t('jobs.chat_failed'));
        loading.value = null;
    }
}
</script>

<template>
    <Head :title="job.title" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto px-4 py-8 space-y-5">

            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <Link
                        :href="backHref"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-navy transition-colors mb-2"
                    >
                        <ArrowLeftIcon class="w-4 h-4 shrink-0" />
                        {{ backLabel }}
                    </Link>
                    <h1 class="text-xl font-bold text-navy leading-tight">{{ job.title }}</h1>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <span v-if="viewer_role === 'owner'">{{ t('jobs.role_owner') }}</span>
                        <span v-else-if="viewer_role === 'accepted_master'">{{ t('jobs.role_accepted_master') }}</span>
                        <span v-else-if="viewer_role === 'applicant'">{{ t('jobs.role_applicant') }}</span>
                        <span v-else-if="viewer_role === 'admin'">{{ t('jobs.role_admin') }}</span>
                    </p>
                </div>
                <JobStatusBadge :status="job.status" :label="job.status_label" class="shrink-0 mt-1" />
            </div>

            <!-- Disputed banner -->
            <div v-if="job.status === 'disputed'" class="flex gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl">
                <ShieldExclamationIcon class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-bold text-red-700">{{ t('jobs.disputed_title') }}</p>
                    <p class="text-xs text-red-600 mt-0.5">{{ t('jobs.disputed_desc') }}</p>
                </div>
            </div>

            <!-- Cancelled banner -->
            <div v-else-if="job.status === 'cancelled'" class="flex gap-3 p-4 bg-gray-50 border border-gray-200 rounded-2xl">
                <XCircleIcon class="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                <p class="text-sm text-gray-500">{{ t('jobs.cancelled_msg') }}</p>
            </div>

            <!-- Category suggestion banner (owner only) -->
            <template v-if="viewer_role === 'owner' && job.pending_category_suggestion">
                <div
                    v-if="job.pending_category_suggestion.status === 'pending'"
                    class="flex items-start gap-2.5 p-3.5 bg-navy/5 border border-navy/10 rounded-2xl"
                >
                    <ClockIcon class="w-4 h-4 mt-0.5 flex-shrink-0 text-navy/60" />
                    <p class="text-sm text-navy/80">
                        {{ t('jobs.suggestion_pending_msg', { name: job.pending_category_suggestion.name }) }}
                    </p>
                </div>
                <div
                    v-else-if="job.pending_category_suggestion.status === 'rejected'"
                    class="flex items-start gap-2.5 p-3.5 bg-red-50 border border-red-200 rounded-2xl"
                >
                    <XCircleIcon class="w-4 h-4 mt-0.5 flex-shrink-0 text-red-500" />
                    <div class="text-sm">
                        <p class="font-semibold text-red-700">
                            {{ t('jobs.suggestion_rejected_title', { name: job.pending_category_suggestion.name }) }}
                        </p>
                        <p v-if="job.pending_category_suggestion.review_note" class="text-red-600 mt-0.5">
                            {{ job.pending_category_suggestion.review_note }}
                        </p>
                    </div>
                </div>
            </template>

            <!-- Timeline -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <JobLifecycleTimeline
                    :job="job"
                    :is-client="viewer_role === 'owner' || viewer_role === 'admin'"
                />
            </div>

            <!-- Proposal sidebar (applicant / accepted_master) -->
            <JobProposalSidebar
                v-if="proposals && (viewer_role === 'applicant' || viewer_role === 'accepted_master')"
                :proposals="proposals"
                :job-id="job.id"
                :current-user-id="currentUserId"
            />

            <!-- Actions panel -->
            <div v-if="showActionPanel" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-navy mb-3">{{ t('jobs.available_actions') }}</h2>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-if="has('pay')"
                        @click="postLifecycle('pay')"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold bg-navy text-white rounded-lg hover:bg-navy/90 disabled:opacity-50 transition-colors"
                    >
                        {{ loading === 'pay' ? t('jobs.action_please_wait') : t('jobs.action_pay') }}
                    </button>
                    <button
                        v-if="has('mark_complete')"
                        @click="postLifecycle('mark_complete')"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold bg-navy text-white rounded-lg hover:bg-navy/90 disabled:opacity-50 transition-colors"
                    >
                        {{ loading === 'mark_complete' ? t('jobs.action_please_wait') : t('jobs.action_mark_complete') }}
                    </button>
                    <button
                        v-if="has('confirm_complete')"
                        @click="postLifecycle('confirm_complete')"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                    >
                        {{ loading === 'confirm_complete' ? t('jobs.action_please_wait') : t('jobs.action_confirm_complete') }}
                    </button>
                    <button
                        v-if="has('withdraw_application')"
                        @click="handleWithdraw"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 disabled:opacity-50 transition-colors"
                    >
                        {{ loading === 'withdraw' ? t('jobs.action_please_wait') : t('jobs.action_withdraw') }}
                    </button>
                    <button
                        v-if="has('resolve_dispute_release')"
                        @click="() => toast.info('Drīzumā...')"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                    >
                        {{ t('jobs.action_release_payment') }}
                    </button>
                    <button
                        v-if="has('resolve_dispute_refund')"
                        @click="() => toast.info('Drīzumā...')"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold bg-amber-500 text-white rounded-lg hover:bg-amber-600 disabled:opacity-50 transition-colors"
                    >
                        {{ t('jobs.action_refund_client') }}
                    </button>
                    <button
                        v-if="has('dispute')"
                        @click="showDisputeModal = true"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 disabled:opacity-50 transition-colors"
                    >
                        {{ t('jobs.action_dispute') }}
                    </button>
                    <button
                        v-if="has('cancel')"
                        @click="showCancelModal = true"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 transition-colors"
                    >
                        {{ t('jobs.action_cancel') }}
                    </button>
                    <button
                        v-if="has('delete_job')"
                        @click="handleDelete"
                        :disabled="!!loading"
                        class="px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 disabled:opacity-50 transition-colors"
                    >
                        {{ loading === 'delete_job' ? t('jobs.action_deleting') : t('jobs.action_delete') }}
                    </button>
                </div>
            </div>

            <!-- Chat -->
            <div v-if="showChat" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-navy">
                            {{ has('chat_with_master') ? t('jobs.chat_with_master') : t('jobs.chat_with_client') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ t('jobs.chat_details') }}</p>
                    </div>
                    <button
                        @click="handleChat"
                        :disabled="loading === 'chat'"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-navy text-white rounded-xl hover:bg-navy/90 disabled:opacity-50 transition-colors shrink-0"
                    >
                        <ChatBubbleLeftRightIcon class="w-4 h-4" />
                        {{ loading === 'chat' ? t('jobs.opening_chat') : t('jobs.open_chat') }}
                    </button>
                </div>
            </div>

            <!-- Applications list (owner / admin) -->
            <div v-if="showAppsSection" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-navy flex items-center gap-2">
                        <ClipboardDocumentListIcon class="w-4 h-4 text-navy/60" />
                        {{ t('jobs.submitted_applications') }}
                    </h2>
                    <span class="text-xs font-bold text-white bg-navy px-2.5 py-1 rounded-full">
                        {{ apps.length }}
                    </span>
                </div>

                <div v-if="apps.length === 0" class="py-8 text-center">
                    <ClipboardDocumentListIcon class="w-8 h-8 text-gray-200 mx-auto mb-2" />
                    <p class="text-sm text-gray-500">{{ t('jobs.no_applications_msg') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ t('jobs.applications_coming_msg') }}</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="app in apps"
                        :key="app.id"
                        class="rounded-xl border p-4 transition-all"
                        :class="{
                            'border-emerald-200 bg-emerald-50/40': app.status === 'accepted',
                            'border-blue-200 bg-blue-50/30':       app.status === 'shortlisted',
                            'border-gray-100 bg-gray-50/50 opacity-60': app.status === 'rejected' || app.status === 'cancelled',
                            'border-gray-100 bg-white':            app.status === 'pending',
                        }"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div class="w-10 h-10 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm shrink-0 overflow-hidden">
                                <img
                                    v-if="app.user.profile?.avatar"
                                    :src="`/storage/${app.user.profile.avatar}`"
                                    :alt="applicantName(app)"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>{{ applicantName(app).slice(0, 2).toUpperCase() }}</span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <a
                                        :href="route('master.public-profile', app.user.id)"
                                        class="text-sm font-bold text-gray-900 hover:text-navy hover:underline transition-colors"
                                    >{{ applicantName(app) }}</a>
                                    <span
                                        class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                        :class="appStatusBadgeClass[app.status]"
                                    >{{ t('statuses.application.' + app.status) }}</span>
                                    <!-- Pending proposal amount badge -->
                                    <span
                                        v-if="app.pending_proposal"
                                        class="flex items-center gap-0.5 text-xs font-bold text-navy"
                                    >
                                        <CurrencyEuroIcon class="w-3.5 h-3.5 text-gold" />
                                        {{ formatMoney(app.pending_proposal.amount) }}
                                    </span>
                                </div>
                                <p v-if="app.user.profile?.city" class="text-xs text-gray-400 mb-1.5">
                                    {{ app.user.profile.city }}
                                </p>
                                <p v-if="app.cover_letter" class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                    {{ app.cover_letter }}
                                </p>
                                <p v-else class="text-xs text-gray-400 italic">{{ t('jobs.no_cover_letter') }}</p>
                            </div>

                            <!-- Per-application buttons (pending / shortlisted, owner only, open jobs only) -->
                            <div
                                v-if="(app.status === 'pending' || app.status === 'shortlisted') && viewer_role === 'owner' && job.status === 'open'"
                                class="flex flex-col gap-1.5 shrink-0"
                            >
                                <button
                                    v-if="app.status === 'pending' && has('shortlist_application')"
                                    @click="handleShortlist(app)"
                                    :disabled="loading === `shortlist-${app.id}`"
                                    class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded-lg disabled:opacity-50 transition-colors"
                                >
                                    <StarIcon class="w-3.5 h-3.5" />
                                    {{ t('jobs.shortlist_btn') }}
                                </button>
                                <!-- Proposal-based accept (shows price inline) - only when master proposed -->
                                <button
                                    v-if="app.pending_proposal && app.pending_proposal.proposed_by.id !== currentUserId"
                                    @click="acceptingProposal = app.pending_proposal"
                                    :disabled="!!loading"
                                    class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg disabled:opacity-50 transition-colors"
                                >
                                    {{ t('jobs.accept_proposal_btn', { price: formatMoney(app.pending_proposal.amount) }) }}
                                </button>
                                <!-- Proposal-based counter - only when master proposed -->
                                <button
                                    v-if="app.pending_proposal && app.pending_proposal.proposed_by.id !== currentUserId"
                                    @click="counteringApp = app"
                                    :disabled="!!loading"
                                    class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-navy bg-white border border-navy/20 hover:bg-navy/5 rounded-lg disabled:opacity-50 transition-colors"
                                >
                                    {{ t('jobs.counter_price_btn') }}
                                </button>
                                <!-- No proposal yet: submit fresh -->
                                <button
                                    v-else-if="!app.pending_proposal"
                                    @click="freshProposalApp = app"
                                    :disabled="!!loading"
                                    class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-navy bg-white border border-navy/20 hover:bg-navy/5 rounded-lg disabled:opacity-50 transition-colors"
                                >
                                    {{ t('jobs.propose_price_btn') }}
                                </button>
                                <!-- Reject proposal - only when master proposed -->
                                <button
                                    v-if="app.pending_proposal && app.pending_proposal.proposed_by.id !== currentUserId"
                                    @click="rejectingProposal = app.pending_proposal"
                                    :disabled="!!loading"
                                    class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg disabled:opacity-50 transition-colors"
                                >
                                    {{ t('jobs.reject_proposal_btn') }}
                                </button>
                                <!-- Withdraw - only when owner proposed it themselves -->
                                <button
                                    v-if="app.pending_proposal && app.pending_proposal.proposed_by.id === currentUserId"
                                    @click="withdrawingProposal = app.pending_proposal"
                                    :disabled="!!loading"
                                    class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg disabled:opacity-50 transition-colors"
                                >
                                    {{ t('jobs.withdraw_proposal_btn') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Own application (applicant) -->
            <div v-if="ownApp && viewer_role === 'applicant'" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-navy mb-4">{{ t('jobs.your_application') }}</h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span
                            class="text-xs font-semibold px-2.5 py-1 rounded-full"
                            :class="appStatusBadgeClass[ownApp.status]"
                        >{{ t('statuses.application.' + ownApp.status) }}</span>
                        <span class="text-xs text-gray-400">{{ formatDate(ownApp.created_at) }}</span>
                    </div>
                    <p v-if="ownApp.cover_letter" class="text-sm text-gray-600 leading-relaxed">
                        {{ ownApp.cover_letter }}
                    </p>
                    <p v-else class="text-xs text-gray-400 italic">{{ t('jobs.no_cover_letter') }}</p>

                    <div v-if="ownApp.status === 'shortlisted'" class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-xs font-semibold text-blue-700">{{ t('jobs.shortlisted_notice_title') }}</p>
                        <p class="text-xs text-blue-600 mt-0.5">{{ t('jobs.shortlisted_notice_desc') }}</p>
                    </div>
                </div>
            </div>

            <!-- Assigned master card (owner) -->
            <div v-if="job.master && (viewer_role === 'owner' || viewer_role === 'admin')" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-navy mb-3">{{ t('jobs.accepted_master_card') }}</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm shrink-0">
                        {{ job.master.name.slice(0, 2).toUpperCase() }}
                    </div>
                    <div>
                        <a
                            :href="route('master.public-profile', job.master.id)"
                            class="text-sm font-bold text-gray-900 hover:text-navy hover:underline transition-colors"
                        >{{ job.master.name }}</a>
                        <p class="text-xs text-gray-400">{{ t('jobs.accepted_master_role_label') }}</p>
                    </div>
                </div>
            </div>

            <!-- Escrow info -->
            <div v-if="job.escrow" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-navy mb-4">{{ t('jobs.escrow_title') }}</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">{{ t('jobs.escrow_amount_label') }}</p>
                        <p class="text-sm font-semibold text-navy">{{ formatMoney(job.escrow.amount) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">{{ t('jobs.escrow_status_label') }}</p>
                        <p
                            class="text-sm font-semibold"
                            :class="{
                                'text-amber-600':   job.escrow.status === 'held',
                                'text-emerald-600': job.escrow.status === 'released',
                                'text-gray-500':    job.escrow.status === 'refunded',
                            }"
                        >
                            {{ t('statuses.escrow.' + job.escrow.status) }}
                        </p>
                    </div>
                    <div v-if="job.escrow.auto_release_at">
                        <p class="text-xs text-gray-500 mb-0.5">{{ t('jobs.escrow_auto_release_label') }}</p>
                        <p class="text-sm text-gray-700">{{ formatDate(job.escrow.auto_release_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Job details -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h2 class="text-sm font-bold text-navy">{{ t('jobs.info_title') }}</h2>
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ job.description }}</p>

                <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                    <!-- Client (non-owner views) -->
                    <div v-if="viewer_role !== 'owner'" class="flex items-start gap-2">
                        <UserIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.client_label') }}</p>
                            <a
                                :href="route('seeker.public-profile', job.client.id)"
                                class="text-sm font-medium text-gray-800 hover:underline hover:text-navy transition-colors"
                            >{{ job.client.name }}</a>
                        </div>
                    </div>

                    <!-- Budget -->
                    <div v-if="job.budget" class="flex items-start gap-2">
                        <CurrencyEuroIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.budget_label') }}</p>
                            <p class="text-sm font-medium text-gray-800">{{ formatMoney(job.budget) }}</p>
                        </div>
                    </div>

                    <!-- Agreed price -->
                    <div v-if="job.agreed_price" class="flex items-start gap-2">
                        <BanknotesIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.agreed_price_label') }}</p>
                            <p class="text-sm font-medium text-gray-800">{{ formatMoney(job.agreed_price, job.price_type) }}</p>
                        </div>
                    </div>

                    <!-- Deadline -->
                    <div v-if="job.deadline" class="flex items-start gap-2">
                        <CalendarIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.deadline_label') }}</p>
                            <p class="text-sm text-gray-700">{{ formatDate(job.deadline) }}</p>
                        </div>
                    </div>

                    <!-- Location -->
                    <div v-if="job.location?.length" class="flex items-start gap-2">
                        <MapPinIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.location_label') }}</p>
                            <p class="text-sm text-gray-700">{{ job.location.join(', ') }}</p>
                        </div>
                    </div>

                    <!-- Created -->
                    <div class="flex items-start gap-2">
                        <ClockIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.created_label') }}</p>
                            <p class="text-sm text-gray-700">{{ formatDate(job.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Completed -->
                    <div v-if="job.completed_at" class="flex items-start gap-2">
                        <CheckCircleIcon class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.completed_label') }}</p>
                            <p class="text-sm text-gray-700">{{ formatDate(job.completed_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div v-if="job.images?.length" class="pt-2 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">{{ t('jobs.photos_label') }}</p>
                    <div class="grid grid-cols-3 gap-2">
                        <img
                            v-for="(img, i) in job.images"
                            :key="i"
                            :src="`/storage/${img}`"
                            class="w-full aspect-square object-cover rounded-lg border border-gray-100"
                        />
                    </div>
                </div>
            </div>

        </div>

        <ConfirmDisputeModal
            :show="showDisputeModal"
            :job-id="job.id"
            @close="showDisputeModal = false"
            @submitted="handleDisputeSubmit"
        />
        <ConfirmCancelModal
            :show="showCancelModal"
            @close="showCancelModal = false"
            @submitted="handleCancelSubmit"
        />

        <!-- Per-application proposal modals (owner) -->
        <ConfirmAcceptProposalModal
            :show="acceptingProposal !== null"
            :proposal="acceptingProposal"
            @close="acceptingProposal = null"
            @confirm="acceptingProposal && handleAcceptProposal(acceptingProposal)"
        />
        <ConfirmRejectProposalModal
            :show="rejectingProposal !== null"
            @close="rejectingProposal = null"
            @confirm="rejectingProposal && handleRejectProposal(rejectingProposal)"
        />
        <ConfirmWithdrawProposalModal
            :show="withdrawingProposal !== null"
            :proposal="withdrawingProposal"
            @close="withdrawingProposal = null"
            @confirm="withdrawingProposal && handleWithdrawProposal(withdrawingProposal)"
        />
        <CounterProposalModal
            :show="counteringApp !== null"
            :current-proposal="counteringApp?.pending_proposal ?? null"
            @close="counteringApp = null"
            @submit="(amount, note) => counteringApp?.pending_proposal && handleCounterProposal(counteringApp.pending_proposal, amount, note)"
        />
        <SubmitFreshProposalModal
            :show="freshProposalApp !== null"
            @close="freshProposalApp = null"
            @submit="(amount, note) => freshProposalApp && handleFreshProposal(freshProposalApp, amount, note)"
        />
    </AuthenticatedLayout>
</template>
