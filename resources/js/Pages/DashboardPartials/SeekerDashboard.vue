<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { formatCurrency, formatDate } from '@/utils/formatters';
import type { AuthUser, JobRequest, JobStatus, ApplicationStatus } from '@/types/models';
import {
    ClipboardDocumentListIcon,
    PlusIcon,
    ChevronRightIcon,
    MapPinIcon,
    CalendarIcon,
    UserGroupIcon,
    ArrowRightIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

const { t } = useI18n();

interface RecentApplicationReceived {
    id: number;
    cover_letter: string | null;
    price_offer: number | null;
    status: ApplicationStatus;
    created_at: string;
    job_request: {
        id: number;
        title: string;
    } | null;
    user: {
        id: number;
        name: string;
        profile: { city: string | null; avatar: string | null } | null;
    };
}

defineProps<{
    stats: Record<string, number>;
    user: AuthUser;
    recentJobRequests: JobRequest[];
    recentApplicationsReceived: RecentApplicationReceived[];
}>();

const jobStatusClasses: Record<JobStatus, string> = {
    open:                  'bg-emerald-100 text-emerald-700',
    accepted:              'bg-blue-100 text-blue-700',
    in_progress:           'bg-yellow-100 text-yellow-800',
    awaiting_confirmation: 'bg-orange-100 text-orange-800',
    completed:             'bg-navy/10 text-navy',
    disputed:              'bg-red-100 text-red-700',
    cancelled:             'bg-gray-100 text-gray-500',
};
</script>

<template>
    <div class="space-y-6">

        
        <div class="bg-navy rounded-2xl overflow-hidden">
            <div class="h-1 w-full bg-emerald-400" />
            <div class="px-8 py-6">
                <p class="text-emerald-400 text-xs font-bold tracking-widest uppercase mb-1">{{ t('dashboard.seeker.panel_label') }}</p>
                <h2 class="text-2xl font-extrabold text-white">{{ t('dashboard.seeker.greeting', { name: user.name }) }}</h2>
                <p class="text-white/50 text-sm mt-1 mb-5">{{ t('dashboard.seeker.subtitle') }}</p>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-2 bg-emerald-400 text-white text-sm font-bold px-4 py-2 rounded-xl hover:bg-emerald-500 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                        {{ t('dashboard.seeker.create_listing') }}
                    </Link>
                    <Link
                        :href="route('seeker.services.index')"
                        class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-white/20 transition-colors"
                    >
                        {{ t('dashboard.seeker.find_masters') }}
                        <ArrowRightIcon class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <ClipboardDocumentListIcon class="w-5 h-5 text-emerald-500" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ t('dashboard.seeker.stats_listings') }}</p>
                    <p class="text-3xl font-extrabold text-navy leading-none mt-1">{{ stats.active_job_requests ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ t('dashboard.seeker.listings_active') }} · {{ t('dashboard.seeker.listings_total', { count: stats.total_job_requests ?? 0 }) }}
                    </p>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-navy hover:underline transition-colors"
                    >
                        {{ t('dashboard.seeker.manage_btn') }}
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                    <UserGroupIcon class="w-5 h-5 text-navy/50" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ t('dashboard.seeker.stats_new_applications') }}</p>
                    <p class="text-3xl font-extrabold text-navy leading-none mt-1">{{ stats.pending_applications_received ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ t('dashboard.seeker.awaiting_response') }}</p>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-navy hover:underline transition-colors"
                    >
                        {{ t('dashboard.seeker.view_btn') }}
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>
            </div>

        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ t('dashboard.seeker.last_listings_title') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.seeker.last_listings_desc') }}</p>
                    </div>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-navy hover:underline transition-colors shrink-0"
                    >
                        {{ t('dashboard.seeker.view_btn') }}
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>

                <div v-if="recentJobRequests.length === 0" class="px-6 py-10 text-center">
                    <div class="mx-auto w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon class="w-5 h-5 text-emerald-300" />
                    </div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ t('dashboard.seeker.no_listings_title') }}</p>
                    <p class="text-xs text-gray-400 mb-4">{{ t('dashboard.seeker.no_listings_desc') }}</p>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1.5 bg-navy text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-navy/90 transition-colors"
                    >
                        <PlusIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                        {{ t('dashboard.seeker.create_listing_btn') }}
                    </Link>
                </div>

                <ul v-else class="divide-y divide-gray-50">
                    <li
                        v-for="job in recentJobRequests"
                        :key="job.id"
                        class="px-6 py-3.5 hover:bg-gray-50/60 transition-colors"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <Link
                                        :href="route('jobs.show', job.id)"
                                        class="text-sm font-semibold text-gray-900 truncate hover:text-navy hover:underline transition-colors"
                                    >{{ job.title }}</Link>
                                    <span
                                        class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="jobStatusClasses[job.status] ?? jobStatusClasses.open"
                                    >
                                        {{ t('statuses.job.' + job.status) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">{{ job.category?.name ?? '-' }}</p>
                                <div class="flex items-center gap-3 mt-1">
                                    <span v-if="job.location?.length" class="inline-flex items-center gap-1 text-xs text-gray-400">
                                        <MapPinIcon class="w-3 h-3" />
                                        {{ job.location[0] }}
                                    </span>
                                    <span v-if="job.deadline" class="inline-flex items-center gap-1 text-xs text-gray-400">
                                        <CalendarIcon class="w-3 h-3" />
                                        {{ formatDate(job.deadline) }}
                                    </span>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <p v-if="job.budget" class="text-sm font-bold text-navy">
                                    {{ formatCurrency(job.budget) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ t('dashboard.seeker.applications_count', job.applications_count ?? 0) }}
                                </p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ t('dashboard.seeker.last_applications_title') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.seeker.last_applications_desc') }}</p>
                    </div>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-navy hover:underline transition-colors shrink-0"
                    >
                        {{ t('dashboard.seeker.manage_btn') }}
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>

                <div v-if="recentApplicationsReceived.length === 0" class="px-6 py-10 text-center">
                    <div class="mx-auto w-11 h-11 rounded-xl bg-navy/5 flex items-center justify-center mb-3">
                        <UserGroupIcon class="w-5 h-5 text-navy/20" />
                    </div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ t('dashboard.seeker.no_new_applications_title') }}</p>
                    <p class="text-xs text-gray-400">{{ t('dashboard.seeker.no_new_applications_desc') }}</p>
                </div>

                <ul v-else class="divide-y divide-gray-50">
                    <li
                        v-for="app in recentApplicationsReceived"
                        :key="app.id"
                        class="flex items-center gap-3 px-6 py-3.5 hover:bg-gray-50/60 transition-colors"
                    >
                        <div class="w-8 h-8 rounded-full bg-navy/10 flex items-center justify-center text-xs font-bold text-navy shrink-0">
                            <span v-if="app.user?.name">{{ app.user.name.charAt(0).toUpperCase() }}</span>
                            <UserIcon v-else class="w-4 h-4 text-navy/40" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ app.user?.name ?? '-' }}</p>
                            <Link
                                v-if="app.job_request"
                                :href="route('jobs.show', app.job_request.id)"
                                class="text-xs text-navy/70 hover:text-navy hover:underline mt-0.5 truncate block transition-colors"
                            >{{ app.job_request.title }}</Link>
                            <p v-else class="text-xs text-gray-400 mt-0.5">-</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p v-if="app.price_offer" class="text-sm font-bold text-navy">
                                {{ formatCurrency(app.price_offer) }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(app.created_at) }}</p>
                        </div>
                    </li>
                </ul>
            </div>

        </div>

    </div>
</template>
