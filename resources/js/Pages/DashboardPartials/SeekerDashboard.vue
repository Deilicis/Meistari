<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
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

const jobStatusConfig: Record<JobStatus, { label: string; classes: string }> = {
    open:                  { label: 'Atvērts',              classes: 'bg-emerald-100 text-emerald-700' },
    accepted:              { label: 'Pieņemts',             classes: 'bg-blue-100 text-blue-700' },
    in_progress:           { label: 'Darbā',                classes: 'bg-yellow-100 text-yellow-800' },
    awaiting_confirmation: { label: 'Gaida apstiprinājumu', classes: 'bg-orange-100 text-orange-800' },
    completed:             { label: 'Pabeigts',             classes: 'bg-navy/10 text-navy' },
    disputed:              { label: 'Strīdā',               classes: 'bg-red-100 text-red-700' },
    cancelled:             { label: 'Atcelts',              classes: 'bg-gray-100 text-gray-500' },
};

const formatDate = (d: string | null) =>
    d ? new Date(d).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' }) : null;

const formatDateTime = (d: string | null) =>
    d ? new Date(d).toLocaleString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : null;

const formatBudget = (budget: number | null) =>
    budget
        ? new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(budget)
        : null;
</script>

<template>
    <div class="space-y-6">

        <!-- Welcome banner -->
        <div class="bg-navy rounded-2xl overflow-hidden">
            <div class="h-1 w-full bg-emerald-400" />
            <div class="px-8 py-6">
                <p class="text-emerald-400 text-xs font-bold tracking-widest uppercase mb-1">Meklētāja panelis</p>
                <h2 class="text-2xl font-extrabold text-white">Sveicināts, {{ user.name }}!</h2>
                <p class="text-white/50 text-sm mt-1 mb-5">
                    Šeit varat publicēt darba sludinājumus, saņemt meistaru piedāvājumus un pārvaldīt pieņemtos pieteikumus.
                </p>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-2 bg-emerald-400 text-white text-sm font-bold px-4 py-2 rounded-xl hover:bg-emerald-500 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                        Izveidot sludinājumu
                    </Link>
                    <Link
                        :href="route('seeker.services.index')"
                        class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-white/20 transition-colors"
                    >
                        Meklēt meistarus
                        <ArrowRightIcon class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <!-- Sludinājumi -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <ClipboardDocumentListIcon class="w-5 h-5 text-emerald-500" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Mani sludinājumi</p>
                    <p class="text-3xl font-extrabold text-navy leading-none mt-1">{{ stats.active_job_requests ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        aktīvi · {{ stats.total_job_requests ?? 0 }} kopā
                    </p>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-navy hover:underline transition-colors"
                    >
                        Pārvaldīt
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>
            </div>

            <!-- Saņemtie pieteikumi -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                    <UserGroupIcon class="w-5 h-5 text-navy/50" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Jauni pieteikumi</p>
                    <p class="text-3xl font-extrabold text-navy leading-none mt-1">{{ stats.pending_applications_received ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">gaida jūsu atbildi</p>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-navy hover:underline transition-colors"
                    >
                        Apskatīt
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>
            </div>

        </div>

        <!-- Two-column: job requests + received applications -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Mani sludinājumi -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Mani sludinājumi</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Pēdējie publicētie darba pieprasījumi</p>
                    </div>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-navy hover:underline transition-colors shrink-0"
                    >
                        Visi
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>

                <div v-if="recentJobRequests.length === 0" class="px-6 py-10 text-center">
                    <div class="mx-auto w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon class="w-5 h-5 text-emerald-300" />
                    </div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Nav neviena sludinājuma</p>
                    <p class="text-xs text-gray-400 mb-4">Publicējiet sludinājumu un aprakstiet nepieciešamo darbu.</p>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1.5 bg-navy text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-navy/90 transition-colors"
                    >
                        <PlusIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                        Izveidot sludinājumu
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
                                        :href="route('seeker.job-requests.show', job.id)"
                                        class="text-sm font-semibold text-gray-900 truncate hover:text-navy hover:underline transition-colors"
                                    >{{ job.title }}</Link>
                                    <span
                                        class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="jobStatusConfig[job.status]?.classes ?? jobStatusConfig.open.classes"
                                    >
                                        {{ jobStatusConfig[job.status]?.label ?? job.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">{{ job.category?.name ?? '—' }}</p>
                                <div class="flex items-center gap-3 mt-1">
                                    <span v-if="job.location?.length" class="inline-flex items-center gap-1 text-xs text-gray-400">
                                        <MapPinIcon class="w-3 h-3" />
                                        {{ job.location[0] }}
                                    </span>
                                    <span v-if="job.deadline" class="inline-flex items-center gap-1 text-xs text-gray-400">
                                        <CalendarIcon class="w-3 h-3" />
                                        {{ formatDateTime(job.deadline) }}
                                    </span>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <p v-if="formatBudget(job.budget)" class="text-sm font-bold text-navy">
                                    {{ formatBudget(job.budget) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ job.applications_count ?? 0 }}
                                    {{ (job.applications_count ?? 0) === 1 ? 'pieteikums' : 'pieteikumi' }}
                                </p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Saņemtie pieteikumi -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Jaunie pieteikumi</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Meistari, kas piesakās jūsu darbiem</p>
                    </div>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-navy hover:underline transition-colors shrink-0"
                    >
                        Pārvaldīt
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>

                <div v-if="recentApplicationsReceived.length === 0" class="px-6 py-10 text-center">
                    <div class="mx-auto w-11 h-11 rounded-xl bg-navy/5 flex items-center justify-center mb-3">
                        <UserGroupIcon class="w-5 h-5 text-navy/20" />
                    </div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Nav jaunu pieteikumu</p>
                    <p class="text-xs text-gray-400">Meistari pieteiks darbus pēc sludinājuma publicēšanas.</p>
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
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ app.user?.name ?? '—' }}</p>
                            <Link
                                v-if="app.job_request"
                                :href="route('seeker.job-requests.show', app.job_request.id)"
                                class="text-xs text-navy/70 hover:text-navy hover:underline mt-0.5 truncate block transition-colors"
                            >{{ app.job_request.title }}</Link>
                            <p v-else class="text-xs text-gray-400 mt-0.5">—</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p v-if="app.price_offer" class="text-sm font-bold text-navy">
                                €{{ app.price_offer }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(app.created_at) }}</p>
                        </div>
                    </li>
                </ul>
            </div>

        </div>

    </div>
</template>
