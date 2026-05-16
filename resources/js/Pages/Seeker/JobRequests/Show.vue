<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import { formatDate, formatCurrency } from '@/utils/formatters';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import JobStatusBadge from '@/Components/Jobs/JobStatusBadge.vue';
import JobLifecycleTimeline from '@/Components/Jobs/JobLifecycleTimeline.vue';
import JobActionButtons from '@/Components/Jobs/JobActionButtons.vue';
import type { JobLifecycle } from '@/types/jobLifecycle';
import type { JobApplication } from '@/types/models';
import {
    ArrowLeftIcon,
    UserIcon,
    CurrencyEuroIcon,
    ClockIcon,
    ShieldExclamationIcon,
    ClipboardDocumentListIcon,
    CheckIcon,
    XCircleIcon,
    CheckBadgeIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const { t } = useI18n();

const props = defineProps<{ job: JobLifecycle }>();

const job = ref<JobLifecycle>(props.job);

watch(() => props.job, (v) => { job.value = v; });

const applications = ref<JobApplication[]>([]);
const loadingApps = ref(false);
const processingApp = ref<number | null>(null);

onMounted(async () => {
    if (job.value.allowed_actions.includes('accept_application')) {
        await fetchApplications();
    }
});

async function fetchApplications() {
    loadingApps.value = true;
    try {
        const { data } = await axios.get(route('api.applications.index', { job_request_id: job.value.id }));
        applications.value = data.data ?? [];
    } catch {
        toast.error(t('jobs.failed_load_apps'));
    } finally {
        loadingApps.value = false;
    }
}

async function acceptApp(app: JobApplication) {
    processingApp.value = app.id;
    try {
        await axios.patch(route('api.applications.accept', app.id));
        toast.success(t('jobs.accept_app_success'));
        router.reload({ only: ['job'] });
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.accept_app_error'));
    } finally {
        processingApp.value = null;
    }
}

async function rejectApp(app: JobApplication) {
    processingApp.value = app.id;
    try {
        await axios.patch(route('api.applications.reject', app.id));
        toast.success(t('jobs.reject_app_success'));
        await fetchApplications();
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('jobs.reject_app_error'));
    } finally {
        processingApp.value = null;
    }
}

function applicantName(app: JobApplication): string {
    const p = app.user.profile;
    if (!p) return app.user.name;
    if (p.type === 'company') return p.company_name ?? app.user.name;
    const parts = [p.first_name, p.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : app.user.name;
}

function formatMoney(amount: string | null, type: string | null): string {
    if (!amount) return '-';
    const n = parseFloat(amount);
    if (isNaN(n)) return '-';
    const formatted = formatCurrency(n);
    return type === 'hourly' ? `${formatted}/h` : formatted;
}
</script>

<template>
    <Head :title="job.title" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

            <!-- Back + header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-navy transition-colors mb-2"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        {{ t('jobs.back_my_jobs') }}
                    </Link>
                    <h1 class="text-xl font-bold text-navy leading-tight">{{ job.title }}</h1>
                </div>
                <JobStatusBadge :status="job.status" :label="job.status_label" />
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <JobLifecycleTimeline :job="job" :is-client="true" />
            </div>

            <!-- Applications (open job awaiting acceptance) -->
            <div v-if="job.allowed_actions.includes('accept_application')" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-navy flex items-center gap-2">
                        <ClipboardDocumentListIcon class="w-4 h-4 text-navy/60" />
                        {{ t('jobs.submitted_applications') }}
                    </h2>
                    <span class="text-xs font-bold text-white bg-navy px-2.5 py-1 rounded-full">
                        {{ applications.length }}
                    </span>
                </div>

                <!-- Loading -->
                <div v-if="loadingApps" class="py-8 text-center">
                    <div class="inline-block w-5 h-5 border-2 border-navy/20 border-t-navy rounded-full animate-spin" />
                </div>

                <!-- Empty -->
                <div v-else-if="applications.length === 0" class="py-8 text-center">
                    <ClipboardDocumentListIcon class="w-8 h-8 text-gray-200 mx-auto mb-2" />
                    <p class="text-sm text-gray-500">{{ t('jobs.no_applications_msg') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ t('jobs.applications_coming_msg') }}</p>
                </div>

                <!-- Cards -->
                <div v-else class="space-y-3">
                    <div
                        v-for="app in applications"
                        :key="app.id"
                        class="rounded-xl border p-4 transition-all"
                        :class="app.status === 'accepted'
                            ? 'border-emerald-200 bg-emerald-50/50'
                            : app.status === 'rejected'
                                ? 'border-gray-100 bg-gray-50 opacity-60'
                                : 'border-gray-100 bg-white'"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div class="w-10 h-10 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden">
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
                                    <span v-if="app.status === 'accepted'" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                                        <CheckBadgeIcon class="w-3.5 h-3.5" />
                                        {{ t('statuses.application.accepted') }}
                                    </span>
                                    <span v-else-if="app.status === 'rejected'" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                        {{ t('statuses.application.rejected') }}
                                    </span>
                                    <span v-else class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                        {{ t('statuses.application.pending') }}
                                    </span>
                                </div>

                                <p v-if="app.user.profile?.city" class="text-xs text-gray-400 mb-2">{{ app.user.profile.city }}</p>

                                <div v-if="app.price_offer !== null" class="flex items-center gap-1.5 text-sm font-semibold text-navy mb-2">
                                    <CurrencyEuroIcon class="w-4 h-4 text-gold" />
                                    {{ formatCurrency(Number(app.price_offer)) }}
                                </div>

                                <p v-if="app.cover_letter" class="text-sm text-gray-600 leading-relaxed line-clamp-3">{{ app.cover_letter }}</p>
                                <p v-else class="text-xs text-gray-400 italic">{{ t('jobs.no_cover_letter') }}</p>
                            </div>

                            <!-- Accept / Reject -->
                            <div v-if="app.status === 'pending'" class="flex flex-col gap-2 flex-shrink-0">
                                <button
                                    @click="acceptApp(app)"
                                    :disabled="processingApp !== null"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <CheckIcon class="w-3.5 h-3.5" />
                                    {{ t('jobs.accept_app_btn') }}
                                </button>
                                <button
                                    @click="rejectApp(app)"
                                    :disabled="processingApp !== null"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <XCircleIcon class="w-3.5 h-3.5" />
                                    {{ t('jobs.reject_proposal_btn') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions (lifecycle buttons - accept_application handled above) -->
            <div
                v-if="job.allowed_actions.filter(a => a !== 'accept_application').length > 0"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5"
            >
                <h2 class="text-sm font-bold text-navy mb-3">{{ t('jobs.available_actions') }}</h2>
                <JobActionButtons :job="job" @updated="job = $event" />
            </div>

            <!-- Escrow info -->
            <div v-if="job.escrow" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-navy mb-4">{{ t('jobs.escrow_title') }}</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">{{ t('jobs.escrow_amount_label') }}</p>
                        <p class="text-sm font-semibold text-navy">{{ formatCurrency(parseFloat(job.escrow.amount)) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">{{ t('jobs.escrow_status_label') }}</p>
                        <p class="text-sm font-semibold" :class="{
                            'text-amber-600': job.escrow.status === 'held',
                            'text-emerald-600': job.escrow.status === 'released',
                            'text-gray-500': job.escrow.status === 'refunded',
                        }">
                            {{ t('statuses.escrow.' + job.escrow.status) }}
                        </p>
                    </div>
                    <div v-if="job.escrow.auto_release_at">
                        <p class="text-xs text-gray-500 mb-0.5">{{ t('jobs.escrow_auto_release_label') }}</p>
                        <p class="text-sm text-gray-700">{{ formatDate(job.escrow.auto_release_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Details card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h2 class="text-sm font-bold text-navy">{{ t('jobs.info_title') }}</h2>

                <p class="text-sm text-gray-700 leading-relaxed">{{ job.description }}</p>

                <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                    <!-- Master -->
                    <div class="flex items-start gap-2">
                        <UserIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.master_label') }}</p>
                            <p v-if="job.master" class="text-sm font-medium text-gray-800">
                                <a :href="route('master.public-profile', job.master.id)" class="hover:underline hover:text-navy transition-colors">
                                    {{ job.master.name }}
                                </a>
                            </p>
                            <p v-else class="text-sm text-gray-400 italic">{{ t('jobs.not_assigned') }}</p>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="flex items-start gap-2">
                        <CurrencyEuroIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">{{ t('jobs.price_label') }}</p>
                            <p v-if="job.agreed_price" class="text-sm font-medium text-gray-800">{{ formatMoney(job.agreed_price, job.price_type) }}</p>
                            <p v-else class="text-sm text-gray-400 italic">{{ t('jobs.price_not_set') }}</p>
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
            </div>

            <!-- Disputed notice -->
            <div
                v-if="job.status === 'disputed'"
                class="flex gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl"
            >
                <ShieldExclamationIcon class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-bold text-red-700">{{ t('jobs.disputed_title') }}</p>
                    <p class="text-xs text-red-600 mt-0.5">{{ t('jobs.disputed_desc_seeker') }}</p>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
