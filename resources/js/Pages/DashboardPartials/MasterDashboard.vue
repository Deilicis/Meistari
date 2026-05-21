<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { formatCurrency, formatDate } from '@/utils/formatters';
import type { AuthUser, Service, ApplicationStatus, JobStatus } from '@/types/models';
import {
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    PlusIcon,
    ChevronRightIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

const { t } = useI18n();

interface RecentApplication {
    id: number;
    cover_letter: string | null;
    price_offer: number | null;
    status: ApplicationStatus;
    created_at: string;
    job_request: {
        id: number;
        title: string;
        status: JobStatus;
        category: { name: string } | null;
    } | null;
}

defineProps<{
    stats: Record<string, number>;
    user: AuthUser;
    recentServices: Service[];
    recentApplications: RecentApplication[];
}>();

const formatPrice = (service: Service): string => {
    if (!service.price) return t('services.price_negotiable');
    return formatCurrency(service.price);
};

const appStatusClasses: Record<ApplicationStatus, string> = {
    pending:     'bg-amber-100 text-amber-700',
    shortlisted: 'bg-indigo-100 text-indigo-700',
    accepted:    'bg-emerald-100 text-emerald-700',
    rejected:    'bg-red-100 text-red-700',
    completed:   'bg-blue-100 text-blue-700',
    cancelled:   'bg-gray-100 text-gray-500',
};
</script>

<template>
    <div class="space-y-6">

        
        <div class="bg-navy rounded-2xl overflow-hidden">
            <div class="h-1 w-full bg-gold" />
            <div class="px-8 py-6">
                <p class="text-gold text-xs font-bold tracking-widest uppercase mb-1">{{ t('dashboard.master.panel_label') }}</p>
                <h2 class="text-2xl font-extrabold text-white">{{ t('dashboard.master.greeting', { name: user.name }) }}</h2>
                <p class="text-white/50 text-sm mt-1 mb-5">{{ t('dashboard.master.subtitle') }}</p>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('master.services.index')"
                        class="inline-flex items-center gap-2 bg-gold text-navy text-sm font-bold px-4 py-2 rounded-xl hover:bg-yellow-400 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                        {{ t('dashboard.master.add_service') }}
                    </Link>
                    <Link
                        :href="route('master.job-requests.categories')"
                        class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-white/20 transition-colors"
                    >
                        {{ t('dashboard.master.find_jobs') }}
                        <ArrowRightIcon class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                    <BriefcaseIcon class="w-5 h-5 text-navy" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ t('dashboard.master.stats_services') }}</p>
                    <p class="text-3xl font-extrabold text-navy leading-none mt-1">{{ stats.total_services ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        <span class="text-emerald-600 font-medium">{{ t('dashboard.master.services_active', { count: stats.active_services ?? 0 }) }}</span>
                        · {{ t('dashboard.master.services_inactive', { count: (stats.total_services ?? 0) - (stats.active_services ?? 0) }) }}
                    </p>
                    <Link
                        :href="route('master.services.index')"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-navy hover:underline transition-colors"
                    >
                        {{ t('dashboard.master.manage_link') }}
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-gold/10 flex items-center justify-center shrink-0">
                    <ClipboardDocumentListIcon class="w-5 h-5 text-gold" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ t('dashboard.master.stats_applications') }}</p>
                    <p class="text-3xl font-extrabold text-navy leading-none mt-1">{{ stats.total_applications ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        <span :class="(stats.pending_applications ?? 0) > 0 ? 'text-amber-600 font-medium' : 'text-gray-400'">
                            {{ t('dashboard.master.pending_applications', { count: stats.pending_applications ?? 0 }) }}
                        </span>
                    </p>
                    <Link
                        :href="route('master.applications.index')"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-navy hover:underline transition-colors"
                    >
                        {{ t('dashboard.master.view_all_link') }}
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>
            </div>

        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ t('dashboard.master.last_services_title') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.master.last_services_desc') }}</p>
                    </div>
                    <Link
                        :href="route('master.services.index')"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-navy hover:underline transition-colors shrink-0"
                    >
                        {{ t('dashboard.master.view_all_link') }}
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>

                <div v-if="recentServices.length === 0" class="px-6 py-10 text-center">
                    <div class="mx-auto w-11 h-11 rounded-xl bg-navy/5 flex items-center justify-center mb-3">
                        <BriefcaseIcon class="w-5 h-5 text-navy/30" />
                    </div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ t('dashboard.master.no_services_title') }}</p>
                    <p class="text-xs text-gray-400 mb-4">{{ t('dashboard.master.no_services_desc') }}</p>
                    <Link
                        :href="route('master.services.index')"
                        class="inline-flex items-center gap-1.5 bg-navy text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-navy/90 transition-colors"
                    >
                        <PlusIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                        {{ t('dashboard.master.add_service_btn') }}
                    </Link>
                </div>

                <ul v-else class="divide-y divide-gray-50">
                    <li
                        v-for="service in recentServices"
                        :key="service.id"
                        class="flex items-center gap-3 px-6 py-3.5 hover:bg-gray-50/60 transition-colors"
                    >
                        <div class="shrink-0">
                            <CheckCircleIcon v-if="service.is_active" class="w-4 h-4 text-emerald-500" />
                            <XCircleIcon v-else class="w-4 h-4 text-gray-300" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ service.title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ service.category?.name ?? '-' }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-bold text-navy">{{ formatPrice(service) }}</p>
                            <p class="text-xs" :class="service.is_active ? 'text-emerald-500' : 'text-gray-400'">
                                {{ service.is_active ? t('dashboard.master.active_badge') : t('dashboard.master.inactive_badge') }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ t('dashboard.master.last_applications_title') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ t('dashboard.master.last_applications_desc') }}</p>
                    </div>
                    <Link
                        :href="route('master.applications.index')"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-navy hover:underline transition-colors shrink-0"
                    >
                        {{ t('dashboard.master.view_all_link') }}
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>

                <div v-if="recentApplications.length === 0" class="px-6 py-10 text-center">
                    <div class="mx-auto w-11 h-11 rounded-xl bg-gold/10 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon class="w-5 h-5 text-gold/50" />
                    </div>
                    <p class="text-sm font-medium text-gray-600 mb-1">{{ t('dashboard.master.no_applications_title') }}</p>
                    <p class="text-xs text-gray-400 mb-4">{{ t('dashboard.master.no_applications_desc') }}</p>
                    <Link
                        :href="route('master.job-requests.categories')"
                        class="inline-flex items-center gap-1.5 bg-navy text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-navy/90 transition-colors"
                    >
                        {{ t('dashboard.master.find_jobs_btn') }}
                    </Link>
                </div>

                <ul v-else class="divide-y divide-gray-50">
                    <li
                        v-for="app in recentApplications"
                        :key="app.id"
                        class="flex items-center gap-3 px-6 py-3.5 hover:bg-gray-50/60 transition-colors"
                    >
                        <ClockIcon class="w-4 h-4 text-gray-300 shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ app.job_request?.title ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ app.job_request?.category?.name ?? '-' }} · {{ formatDate(app.created_at) }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                            :class="appStatusClasses[app.status as ApplicationStatus] ?? 'bg-gray-100 text-gray-500'"
                        >
                            {{ t('statuses.application.' + app.status) }}
                        </span>
                    </li>
                </ul>
            </div>

        </div>

    </div>
</template>
