<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { AuthUser, JobRequest, JobStatus } from '@/types/models';
import {
    ClipboardDocumentListIcon,
    PlusIcon,
    ChevronRightIcon,
    MapPinIcon,
    CalendarIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';

defineProps<{
    stats: Record<string, number>;
    user: AuthUser;
    recentJobRequests: JobRequest[];
}>();

const statusConfig: Record<JobStatus, { label: string; classes: string }> = {
    active: { label: 'Aktīvs',    classes: 'bg-emerald-100 text-emerald-700' },
    assigned: { label: 'Piešķirts', classes: 'bg-blue-100 text-blue-700' },
    completed: { label: 'Pabeigts',  classes: 'bg-navy/10 text-navy' },
    cancelled: { label: 'Atcelts',   classes: 'bg-red-100 text-red-700' },
};

const formatDate = (d: string | null) =>
    d ? new Date(d).toLocaleDateString('lv-LV', { year: 'numeric', month: 'short', day: 'numeric' }) : null;

const formatBudget = (budget: number | null) =>
    budget
        ? new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(budget)
        : null;
</script>

<template>
    <div class="space-y-6">

        <!-- Sveiciena josla -->
        <div class="bg-navy rounded-2xl overflow-hidden">
            <div class="h-1 w-full bg-emerald-400" />
            <div class="px-8 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-emerald-400 text-xs font-bold tracking-widest uppercase mb-1">Meklētāja panelis</p>
                    <h2 class="text-2xl font-extrabold text-white">Sveicināts, {{ user.name }}!</h2>
                    <p class="text-white/50 text-sm mt-1">Publicējiet sludinājumus un saņemiet piedāvājumus no meistariem.</p>
                </div>
                <Link :href="route('seeker.job-requests.index')"
                    class="inline-flex items-center gap-2 bg-emerald-400 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-emerald-500 transition-colors shrink-0">
                    <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                    Izveidot sludinājumu
                </Link>
            </div>
        </div>

        <!-- Statistikas kartītes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <ClipboardDocumentListIcon class="w-6 h-6 text-emerald-500" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Aktīvie sludinājumi</p>
                    <p class="text-3xl font-extrabold text-navy leading-none">{{ stats.active_job_requests ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ stats.total_job_requests ?? 0 }} kopā</p>
                    <Link :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-navy hover:text-navy-hover transition-colors">
                        Pārvaldīt
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                    <UserGroupIcon class="w-6 h-6 text-navy/40" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Meistaru piedāvājumi</p>
                    <p class="text-3xl font-extrabold text-navy leading-none">0</p>
                    <span class="inline-flex items-center mt-3 text-xs font-medium text-gray-300">
                        Drīzumā pieejams
                    </span>
                </div>
            </div>

        </div>

        <!-- Neseni sludinājumi -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900">Mani sludinājumi</h3>
                <Link :href="route('seeker.job-requests.index')"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-navy hover:text-navy-hover transition-colors">
                    Skatīt visus
                    <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                </Link>
            </div>

            <!-- Nav neviena sludinājuma -->
            <div v-if="recentJobRequests.length === 0" class="px-6 py-10 text-center">
                <div class="mx-auto w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                    <ClipboardDocumentListIcon class="w-6 h-6 text-emerald-300" />
                </div>
                <p class="text-sm font-medium text-gray-600 mb-1">Nav neviena sludinājuma</p>
                <p class="text-xs text-gray-400 mb-4">Publicējiet sludinājumu un aprakstiet nepieciešamo darbu.</p>
                <Link :href="route('seeker.job-requests.index')"
                    class="inline-flex items-center gap-2 bg-navy text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-navy-hover transition-colors">
                    <PlusIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    Izveidot sludinājumu
                </Link>
            </div>

            <!-- Sludinājumu saraksts -->
            <ul v-else class="divide-y divide-gray-50">
                <li v-for="job in recentJobRequests" :key="job.id"
                    class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50/60 transition-colors">

                    <!-- Informācija -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ job.title }}</p>
                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="statusConfig[job.status]?.classes ?? statusConfig.active.classes">
                                {{ statusConfig[job.status]?.label ?? job.status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400">{{ job.category?.name ?? '—' }}</p>
                        <div class="flex items-center gap-3 mt-1.5">
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

                    <!-- Budžets + pieteikumu skaits -->
                    <div class="shrink-0 text-right">
                        <p v-if="formatBudget(job.budget)" class="text-sm font-bold text-navy">
                            {{ formatBudget(job.budget) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ job.applications_count ?? 0 }} pieteikum{{ (job.applications_count ?? 0) === 1 ? 's' : 'i' }}
                        </p>
                    </div>
                </li>
            </ul>
        </div>

    </div>
</template>