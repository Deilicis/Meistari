<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import LeaveReviewModal from '@/Components/Common/LeaveReviewModal.vue';
import JobRequestDetailModal from '@/Components/JobRequests/JobRequestDetailModal.vue';
import type { ApplicationWithJobRequest, ApplicationStatus, JobRequestWithSeeker } from '@/types/models';
import {
    ClipboardDocumentListIcon,
    ArrowTopRightOnSquareIcon,
    XCircleIcon,
    StarIcon,
    BanknotesIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    applications: ApplicationWithJobRequest[];
}>();

const activeFilter = ref<ApplicationStatus | 'all'>('all');

const statusConfig: Record<ApplicationStatus, { label: string; badgeClass: string; borderClass: string }> = {
    pending:   { label: 'Gaida apstiprinājumu', badgeClass: 'bg-amber-100 text-amber-700',   borderClass: 'border-l-amber-400' },
    accepted:  { label: 'Pieņemts',             badgeClass: 'bg-emerald-100 text-emerald-700', borderClass: 'border-l-emerald-500' },
    rejected:  { label: 'Noraidīts',            badgeClass: 'bg-red-100 text-red-700',        borderClass: 'border-l-red-400' },
    completed: { label: 'Pabeigts',             badgeClass: 'bg-blue-100 text-blue-700',      borderClass: 'border-l-blue-400' },
    cancelled: { label: 'Atcelts',              badgeClass: 'bg-gray-100 text-gray-500',      borderClass: 'border-l-gray-300' },
};

const tabs: { key: ApplicationStatus | 'all'; label: string }[] = [
    { key: 'all',       label: 'Visi' },
    { key: 'pending',   label: 'Gaida' },
    { key: 'accepted',  label: 'Pieņemti' },
    { key: 'rejected',  label: 'Noraidīti' },
    { key: 'cancelled', label: 'Atcelti' },
];

const filteredApplications = computed(() => {
    if (activeFilter.value === 'all') return props.applications;
    return props.applications.filter(a => a.status === activeFilter.value);
});

const tabCount = (key: ApplicationStatus | 'all') => {
    if (key === 'all') return props.applications.length;
    return props.applications.filter(a => a.status === key).length;
};

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });

const seekerName = (app: ApplicationWithJobRequest): string => {
    const profile = app.job_request?.user?.profile;
    const user = app.job_request?.user;
    if (!profile) return user?.name ?? '—';
    if (profile.type === 'company') return profile.company_name ?? user?.name ?? '—';
    const parts = [profile.first_name, profile.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : (user?.name ?? '—');
};

// Job detail modal
const detailJob = ref<JobRequestWithSeeker | null>(null);
const openJobDetail = (app: ApplicationWithJobRequest) => {
    if (app.job_request) detailJob.value = app.job_request as JobRequestWithSeeker;
};

// Review modal
const reviewJobRequestId = ref<number | null>(null);
const revieweeId = ref<number | null>(null);
const revieweeName = ref('');

const openReview = (app: ApplicationWithJobRequest) => {
    revieweeId.value = app.job_request?.user?.id ?? null;
    reviewJobRequestId.value = app.job_request?.id ?? null;
    const profile = app.job_request?.user?.profile;
    const user = app.job_request?.user;
    if (profile?.type === 'company') {
        revieweeName.value = profile.company_name ?? user?.name ?? '';
    } else {
        const parts = [profile?.first_name, profile?.last_name].filter(Boolean);
        revieweeName.value = parts.length ? parts.join(' ') : (user?.name ?? '');
    }
};

const closeReview = () => {
    revieweeId.value = null;
    reviewJobRequestId.value = null;
    revieweeName.value = '';
};

// Cancel
const cancelTarget = ref<ApplicationWithJobRequest | null>(null);
const cancelling = ref(false);

const confirmCancel = async () => {
    if (!cancelTarget.value) return;
    cancelling.value = true;
    try {
        await axios.delete(route('api.applications.destroy', cancelTarget.value.id));
        toast.success('Pieteikums atcelts.');
        cancelTarget.value = null;
        router.reload({ only: ['applications'] });
    } catch {
        toast.error('Neizdevās atcelt pieteikumu.');
    } finally {
        cancelling.value = false;
    }
};
</script>

<template>
    <Head title="Mani Pieteikumi" />

    <AuthenticatedLayout>

        <!-- Page header -->
        <div class="bg-navy">
            <div class="h-1 bg-gold" />
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <ClipboardDocumentListIcon class="w-6 h-6 text-gold" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">Mani pieteikumi</h1>
                        <p class="text-white/50 text-sm mt-0.5">
                            Pieteikumi uz darba sludinājumiem ·
                            <span class="text-gold font-semibold">{{ applications.length }}</span> kopā
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <!-- Filter tabs -->
            <div class="flex gap-1 flex-wrap border-b border-gray-200">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeFilter = tab.key"
                    class="px-3 py-2 text-sm font-medium rounded-t transition-colors relative -mb-px"
                    :class="activeFilter === tab.key
                        ? 'text-navy border-b-2 border-navy bg-navy/5'
                        : 'text-gray-500 hover:text-navy hover:bg-gray-50'"
                >
                    {{ tab.label }}
                    <span
                        v-if="tabCount(tab.key) > 0"
                        class="ml-1.5 text-xs font-bold px-1.5 py-0.5 rounded-full"
                        :class="activeFilter === tab.key ? 'bg-navy text-white' : 'bg-gray-200 text-gray-600'"
                    >
                        {{ tabCount(tab.key) }}
                    </span>
                </button>
            </div>

            <!-- Application cards -->
            <div v-if="filteredApplications.length > 0" class="space-y-3">
                <div
                    v-for="app in filteredApplications"
                    :key="app.id"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                >
                    <!-- Top bar: title, category, status, date -->
                    <div
                        class="px-5 pt-5 pb-4 border-l-4"
                        :class="statusConfig[app.status].borderClass"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                    <h3 class="font-bold text-navy text-base leading-snug">
                                        {{ app.job_request?.title ?? '—' }}
                                    </h3>
                                    <span
                                        v-if="app.job_request?.category"
                                        class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
                                    >
                                        {{ app.job_request.category.name }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                    <UserIcon class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                    <a
                                        v-if="app.job_request?.user?.id"
                                        :href="route('seeker.public-profile', app.job_request.user.id)"
                                        class="font-medium text-gray-700 hover:text-navy hover:underline transition-colors"
                                    >{{ seekerName(app) }}</a>
                                    <span v-else class="font-medium text-gray-700">{{ seekerName(app) }}</span>
                                    <span v-if="app.job_request?.user?.profile?.city" class="text-gray-400">
                                        · {{ app.job_request.user.profile.city }}
                                    </span>
                                </div>
                            </div>
                            <div class="shrink-0 flex flex-col items-end gap-1.5">
                                <span
                                    class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                    :class="statusConfig[app.status].badgeClass"
                                >
                                    {{ statusConfig[app.status].label }}
                                </span>
                                <span class="text-xs text-gray-400">{{ formatDate(app.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Body: cover letter + price meta -->
                    <div class="px-5 pb-4 space-y-3">
                        <p v-if="app.cover_letter" class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                            {{ app.cover_letter }}
                        </p>
                        <p v-else class="text-sm text-gray-400 italic">Nav pievienota vēstule.</p>

                        <div class="flex items-center gap-5 flex-wrap text-sm">
                            <span
                                v-if="app.job_request?.budget != null"
                                class="flex items-center gap-1.5 text-gray-500"
                            >
                                <BanknotesIcon class="w-3.5 h-3.5 text-gray-400" />
                                Budžets:
                                <span class="font-semibold text-gray-800">€{{ app.job_request.budget }}</span>
                            </span>
                            <span
                                v-if="app.price_offer != null"
                                class="flex items-center gap-1.5 text-gray-500"
                            >
                                <BanknotesIcon class="w-3.5 h-3.5 text-emerald-500" />
                                Mans piedāvājums:
                                <span class="font-semibold text-emerald-700">€{{ app.price_offer }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Action bar -->
                    <div class="bg-gray-50 border-t border-gray-100 px-5 py-3 flex items-center justify-between gap-3">
                        <button
                            v-if="app.job_request"
                            @click="openJobDetail(app)"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-navy hover:underline transition-colors"
                        >
                            <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                            Skatīt sludinājumu
                        </button>
                        <span v-else />

                        <div class="flex items-center gap-2">
                            <button
                                v-if="app.status === 'pending'"
                                @click="cancelTarget = app"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 hover:border-red-300 rounded-lg px-3 py-1.5 transition-colors"
                            >
                                <XCircleIcon class="w-3.5 h-3.5" />
                                Atcelt pieteikumu
                            </button>
                            <button
                                v-if="app.status === 'completed' && app.job_request?.status === 'completed'"
                                @click="openReview(app)"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-navy bg-navy/5 border border-navy/20 hover:bg-navy/10 rounded-lg px-3 py-1.5 transition-colors"
                            >
                                <StarIcon class="w-3.5 h-3.5" />
                                Atstāt atsauksmi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-16 text-center">
                <div class="mx-auto w-12 h-12 rounded-xl bg-gold/10 flex items-center justify-center mb-4">
                    <ClipboardDocumentListIcon class="w-6 h-6 text-gold/60" />
                </div>
                <p class="text-base font-semibold text-gray-700 mb-1">
                    {{ activeFilter === 'all' ? 'Vēl nav neviena pieteikuma' : 'Nav pieteikumu šajā kategorijā' }}
                </p>
                <p class="text-sm text-gray-400">
                    {{ activeFilter === 'all' ? 'Atrodiet darba sludinājumus un pieteicieties!' : 'Izmēģiniet citu filtru.' }}
                </p>
            </div>

        </div>
    </AuthenticatedLayout>

    <ConfirmDialog
        :show="cancelTarget !== null"
        title="Atcelt pieteikumu?"
        :message="cancelTarget ? `Vai tiešām vēlaties atcelt pieteikumu sludinājumam &quot;${cancelTarget.job_request?.title ?? ''}&quot;?` : ''"
        confirmLabel="Atcelt pieteikumu"
        :processing="cancelling"
        @confirm="confirmCancel"
        @cancel="cancelTarget = null"
    />

    <JobRequestDetailModal
        :show="detailJob !== null"
        :job="detailJob"
        :applied="true"
        @close="detailJob = null"
        @apply="() => {}"
    />

    <LeaveReviewModal
        :show="revieweeId !== null"
        :job-request-id="reviewJobRequestId"
        :reviewee-id="revieweeId"
        :reviewee-name="revieweeName"
        @close="closeReview"
        @submitted="closeReview"
    />
</template>
