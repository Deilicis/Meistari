<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import { formatDate, formatCurrency } from '@/utils/formatters';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import JobRequestModal from '@/Components/JobRequests/JobRequestModal.vue';
import JobApplicationsModal from '@/Components/JobRequests/JobApplicationsModal.vue';
import LeaveReviewModal from '@/Components/Common/LeaveReviewModal.vue';
import MyJobRequestsSearchBar from '@/Components/Search/MyJobRequestsSearchBar.vue';
import { useDebouncedFilter } from '@/composables/useDebouncedFilter';
import { useConfirmDelete } from '@/composables/useConfirmDelete';
import { PlusIcon, MapPinIcon, CalendarIcon, PencilIcon, TrashIcon, UsersIcon, ClipboardDocumentListIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
import * as HeroIcons from '@heroicons/vue/24/outline';
import type { JobRequest, Category, JobStatus, JobApplication } from '@/types/models';

const { t } = useI18n();

const props = defineProps<{
    jobRequests: JobRequest[];
    categories: Category[];
    filters?: {
        search?: string;
        category_id?: string;
        status?: string;
        budget_min?: string;
        budget_max?: string;
    };
}>();

const { filterForm, clearFilters, hasActiveFilters } = useDebouncedFilter(
    'seeker.job-requests.index',
    {
        search: props.filters?.search      ?? '',
        category_id: props.filters?.category_id ?? '',
        status: props.filters?.status      ?? '',
        budget_min: props.filters?.budget_min  ?? '',
        budget_max: props.filters?.budget_max  ?? '',
    },
    { search: '', category_id: '', status: '', budget_min: '', budget_max: '' }
);

const statusClasses: Record<JobStatus, string> = {
    open:                  'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
    accepted:              'bg-blue-100 text-blue-800 ring-1 ring-blue-200',
    in_progress:           'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-200',
    awaiting_confirmation: 'bg-orange-100 text-orange-800 ring-1 ring-orange-200',
    completed:             'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200',
    disputed:              'bg-red-100 text-red-800 ring-1 ring-red-200',
    cancelled:             'bg-gray-100 text-gray-500 ring-1 ring-gray-200',
};

const isJobModalOpen = ref(false);
const jobToEdit = ref<JobRequest | null>(null);

const refreshJobs = () => {
    toast.success(t('jobs.saved_toast'));
    router.reload({ only: ['jobRequests'] });
};

const openEditModal = (job: JobRequest) => {
    jobToEdit.value = job;
    isJobModalOpen.value = true;
};

const {
    deleteTarget: jobToDelete,
    isDeleting: isDeleteProcessing,
    confirmDelete,
    cancelDelete,
    executeDelete,
} = useConfirmDelete<number>({
    routeName: 'api.job-requests.destroy',
    getRouteId: (id) => id,
    successMessage: t('jobs.deleted_toast'),
    errorMessage: t('jobs.delete_failed'),
});

// Applications modal
const applicationsJob = ref<JobRequest | null>(null);
const applicationsData = ref<JobApplication[]>([]);
const loadingApplications = ref(false);

const openApplications = async (job: JobRequest) => {
    loadingApplications.value = true;
    applicationsJob.value = job;
    try {
        const { data } = await axios.get(route('api.applications.index', { job_request_id: job.id }));
        applicationsData.value = data.data ?? [];
    } catch {
        toast.error(t('jobs.failed_load_apps'));
        applicationsJob.value = null;
    } finally {
        loadingApplications.value = false;
    }
};

const closeApplications = () => {
    applicationsJob.value = null;
    applicationsData.value = [];
};

const onApplicationsUpdated = async () => {
    if (applicationsJob.value) {
        await openApplications(applicationsJob.value);
    }
    router.reload({ only: ['jobRequests'] });
};

// Review modal
const reviewJobRequestId = ref<number | null>(null);
const revieweeId = ref<number | null>(null);
const revieweeName = ref('');

const openReview = (revieweeUserId: number, jobRequestId: number, name: string) => {
    revieweeId.value = revieweeUserId;
    reviewJobRequestId.value = jobRequestId;
    revieweeName.value = name;
};

const closeReview = () => {
    revieweeId.value = null;
    reviewJobRequestId.value = null;
    revieweeName.value = '';
};

const categoryIcon = (job: JobRequest) => {
    const icon = job.category?.icon;
    if (!icon) return null;
    return (HeroIcons as Record<string, unknown>)[icon] ?? null;
};

</script>

<template>
    <Head :title="t('jobs.my_title')" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1 bg-emerald-400" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <ClipboardDocumentListIcon class="w-6 h-6 text-emerald-400" />
                        <div>
                            <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ t('jobs.my_title') }}</h1>
                            <p class="text-white/50 text-sm mt-0.5">
                                <span class="text-emerald-400 font-semibold">{{ t('jobs.my_count', jobRequests.length) }}</span>
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('jobs.create')"
                        class="inline-flex items-center gap-2 bg-emerald-400 text-white text-sm font-bold px-4 py-2 rounded-xl hover:bg-emerald-500 transition-colors shrink-0"
                    >
                        <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                        {{ t('jobs.new_listing_btn') }}
                    </Link>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <MyJobRequestsSearchBar v-model="filterForm" :categories="categories" />

                <EmptyState
                    v-if="!jobRequests || jobRequests.length === 0"
                    :title="hasActiveFilters() ? t('jobs.empty_filter_title') : t('jobs.empty_title')"
                    :description="hasActiveFilters() ? t('jobs.empty_filter_desc') : t('jobs.empty_desc')"
                >
                    <template #icon>
                        <svg class="w-8 h-8 text-navy/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </template>
                    <template #action>
                        <button
                            v-if="hasActiveFilters()"
                            @click="clearFilters"
                            class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-navy-hover transition-colors"
                        >
                            {{ t('jobs.clear_filters_btn') }}
                        </button>
                        <Link
                            v-else
                            :href="route('jobs.create')"
                            class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-navy-hover transition-colors"
                        >
                            {{ t('jobs.create_first_btn') }}
                        </Link>
                    </template>
                </EmptyState>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="job in jobRequests"
                        :key="job.id"
                        class="group bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow flex items-stretch"
                    >
                        <!-- Left: category icon -->
                        <div class="w-16 shrink-0 flex items-center justify-center px-3 border-r border-gray-100">
                            <div class="w-10 h-10 rounded-xl bg-navy/5 flex items-center justify-center">
                                <component :is="categoryIcon(job)" v-if="categoryIcon(job)" class="w-5 h-5 text-navy/70" />
                                <svg v-else class="w-5 h-5 text-navy/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>

                        <!-- Middle: category, status, title, description, chips -->
                        <div class="flex-1 min-w-0 px-4 py-4">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span v-if="job.category" class="text-xs font-semibold text-gray-400 tracking-wide uppercase">
                                    {{ job.category.name }}
                                </span>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="statusClasses[job.status] ?? statusClasses.open"
                                >
                                    {{ t('statuses.job.' + job.status) }}
                                </span>
                            </div>
                            <Link
                                :href="route('jobs.show', job.id)"
                                class="text-sm font-bold text-navy mb-1 line-clamp-1 hover:underline block"
                            >{{ job.title }}</Link>
                            <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ job.description }}</p>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-if="job.location?.length"
                                    class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 rounded px-1.5 py-0.5 border border-gray-100"
                                >
                                    <MapPinIcon class="w-2.5 h-2.5 text-gray-400" />
                                    {{ job.location[0] }}
                                </span>
                                <span
                                    v-if="job.deadline"
                                    class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 rounded px-1.5 py-0.5 border border-gray-100"
                                >
                                    <CalendarIcon class="w-2.5 h-2.5 text-gray-400" />
                                    {{ formatDate(job.deadline) }}
                                </span>
                            </div>
                        </div>

                        <!-- Right: budget + app count + actions -->
                        <div class="shrink-0 flex flex-col items-end justify-between px-4 py-4 min-w-[110px]">
                            <div class="text-right">
                                <span v-if="job.budget" class="text-sm font-bold text-navy block">{{ formatCurrency(job.budget) }}</span>
                                <button
                                    v-if="job.status !== 'cancelled'"
                                    @click="openApplications(job)"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-full px-3 py-1 transition-colors mt-0.5"
                                    :class="(job.applications_count ?? 0) > 0
                                        ? 'bg-navy text-white hover:bg-navy-hover'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                >
                                    <UsersIcon class="w-3.5 h-3.5" />
                                    {{ t('jobs.applications_count_label', { count: job.applications_count ?? 0 }) }}
                                </button>
                                <span v-else-if="job.status === 'cancelled'" class="inline-flex items-center gap-1.5 text-xs font-medium bg-gray-100 text-gray-400 rounded-full px-3 py-1 mt-0.5">
                                    <UsersIcon class="w-3.5 h-3.5" />
                                    {{ t('jobs.applications_count_label', { count: job.applications_count ?? 0 }) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Link
                                    :href="route('jobs.show', job.id)"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-navy hover:bg-navy/5 transition-colors"
                                    :title="t('jobs.view_job')"
                                >
                                    <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                                </Link>
                                <button
                                    v-if="job.status === 'open'"
                                    @click="openEditModal(job)"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-navy hover:bg-navy/5 transition-colors"
                                    :title="t('jobs.edit_btn')"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button
                                    v-if="job.status === 'open'"
                                    @click="confirmDelete(job.id)"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    :title="t('jobs.delete_btn')"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <JobRequestModal
            :show="isJobModalOpen"
            :jobRequest="jobToEdit"
            :categories="categories"
            @close="isJobModalOpen = false"
            @saved="refreshJobs"
        />

        <ConfirmDialog
            :show="jobToDelete !== null"
            :title="t('jobs.delete_confirm_title')"
            :message="t('jobs.delete_confirm_message')"
            :confirmLabel="t('jobs.confirm_delete_btn')"
            :processing="isDeleteProcessing"
            @confirm="executeDelete"
            @cancel="cancelDelete"
        />

    </AuthenticatedLayout>

    <JobApplicationsModal
        :show="applicationsJob !== null"
        :job="applicationsJob"
        :applications="applicationsData"
        @close="closeApplications"
        @updated="onApplicationsUpdated"
        @review="(revieweeUserId, jobReqId) => {
            const acceptedApp = applicationsData.find(a => a.status === 'accepted');
            openReview(revieweeUserId, jobReqId, acceptedApp ? (acceptedApp.user.profile?.first_name ?? acceptedApp.user.name) : '');
        }"
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
