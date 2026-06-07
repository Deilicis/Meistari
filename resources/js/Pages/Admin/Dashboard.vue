<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    UserGroupIcon,
    WrenchScrewdriverIcon,
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    ExclamationTriangleIcon,
    ShieldCheckIcon,
    UsersIcon,
    BanknotesIcon,
    TagIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

const { t } = useI18n();

interface RecentComplaint {
    id: number;
    status: string;
    created_at: string;
    reporter: { id: number; name: string };
    reported_user: { id: number; name: string };
}

interface RecentAuditLog {
    id: number;
    action: string;
    auditable_type: string;
    created_at: string;
    user: { id: number; name: string } | null;
}

const props = defineProps<{
    stats: {
        totalUsers: number;
        seekers: number;
        masters: number;
        moderators: number;
        services: number;
        jobRequests: number;
        pendingComplaints: number;
        activeDisputes: number;
        pendingSuggestions: number;
        escrowHeld: number;
    };
    jobsByStatus: Record<string, number>;
    jobsOverTime: Array<{ week: string; count: number }>;
    needsAttention: {
        pendingComplaints: number;
        activeDisputes: number;
        pendingSuggestions: number;
    };
    recentComplaints: RecentComplaint[];
    recentAuditLogs: RecentAuditLog[];
}>();

const statCards = computed(() => [
    { label: t('admin.dashboard.total_users'), value: props.stats.totalUsers, icon: UsersIcon, bg: 'bg-slate-100', iconColor: 'text-slate-600' },
    { label: t('admin.dashboard.seekers'), value: props.stats.seekers, icon: UserGroupIcon, bg: 'bg-blue-100', iconColor: 'text-blue-700', href: route('admin.seekers.index') },
    { label: t('admin.dashboard.masters'), value: props.stats.masters, icon: WrenchScrewdriverIcon, bg: 'bg-amber-100', iconColor: 'text-amber-700', href: route('admin.masters.index') },
    { label: t('admin.dashboard.moderators'), value: props.stats.moderators, icon: ShieldCheckIcon, bg: 'bg-orange-100', iconColor: 'text-orange-700', href: route('admin.staff.index') },
    { label: t('admin.dashboard.services'), value: props.stats.services, icon: BriefcaseIcon, bg: 'bg-emerald-100', iconColor: 'text-emerald-700', href: route('admin.services.index') },
    { label: t('admin.dashboard.job_requests'), value: props.stats.jobRequests, icon: ClipboardDocumentListIcon, bg: 'bg-purple-100', iconColor: 'text-purple-700', href: route('admin.job-requests.index') },
    {
        label: t('admin.dashboard.pending_complaints'),
        value: props.stats.pendingComplaints,
        icon: ExclamationTriangleIcon,
        bg: props.stats.pendingComplaints > 0 ? 'bg-red-100' : 'bg-gray-100',
        iconColor: props.stats.pendingComplaints > 0 ? 'text-red-600' : 'text-gray-400',
        href: route('admin.complaints.index'),
    },
    {
        label: t('admin.dashboard.escrow_held'),
        value: formatCurrency(props.stats.escrowHeld),
        icon: BanknotesIcon,
        bg: 'bg-teal-100',
        iconColor: 'text-teal-700',
        isString: true,
    },
]);

function formatCurrency(n: number): string {
    return new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(n);
}

const jobStatusConfig: Record<string, { label: string; color: string }> = {
    open:  { label: t('admin.dashboard.status_open'), color: '#10b981' },
    accepted: { label: t('admin.dashboard.status_accepted'), color: '#3b82f6' },
    in_progress: { label: t('admin.dashboard.status_in_progress'), color: '#f59e0b' },
    awaiting_confirmation: { label: t('admin.dashboard.status_awaiting'), color: '#f97316' },
    completed: { label: t('admin.dashboard.status_completed'), color: '#6366f1' },
    disputed: { label: t('admin.dashboard.status_disputed'), color: '#ef4444' },
    cancelled: { label: t('admin.dashboard.status_cancelled'), color: '#9ca3af' },
};

const totalJobs = computed(() => Object.values(props.jobsByStatus).reduce((s, c) => s + c, 0) || 1);

const donutSegments = computed(() => {
    let offset = 0;
    return Object.entries(props.jobsByStatus)
        .filter(([, count]) => count > 0)
        .map(([status, count]) => {
            const pct = (count / totalJobs.value) * 100;
            const seg = { status, count, pct, offset };
            offset += pct;
            return seg;
        });
});

function donutPath(pct: number, offset: number): string {
    const r = 40;
    const cx = 50;
    const cy = 50;
    const startAngle = (offset / 100) * 2 * Math.PI - Math.PI / 2;
    const endAngle = ((offset + pct) / 100) * 2 * Math.PI - Math.PI / 2;
    const x1 = cx + r * Math.cos(startAngle);
    const y1 = cy + r * Math.sin(startAngle);
    const x2 = cx + r * Math.cos(endAngle);
    const y2 = cy + r * Math.sin(endAngle);
    const largeArc = pct > 50 ? 1 : 0;
    return `M ${cx} ${cy} L ${x1} ${y1} A ${r} ${r} 0 ${largeArc} 1 ${x2} ${y2} Z`;
}

const maxWeekCount = computed(() => Math.max(...props.jobsOverTime.map(w => w.count), 1));

function weekLabel(iso: string): string {
    const d = new Date(iso);
    return `${d.getDate()}.${d.getMonth() + 1}`;
}

const complaintStatusBadge = (status: string) => {
    const map: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        reviewed: 'bg-blue-100 text-blue-800',
        resolved: 'bg-green-100 text-green-800',
        dismissed: 'bg-gray-100 text-gray-600',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};

const complaintStatusLabel = (status: string) => {
    const map: Record<string, string> = {
        pending: t('admin.dashboard.complaint_pending'),
        reviewed: t('admin.dashboard.complaint_reviewed'),
        resolved: t('admin.dashboard.complaint_resolved'),
        dismissed: t('admin.dashboard.complaint_dismissed'),
    };
    return map[status] ?? status;
};

const auditActionBadge = (action: string) => {
    const map: Record<string, string> = {
        created: 'text-emerald-700 bg-emerald-50',
        updated: 'text-blue-700 bg-blue-50',
        deleted: 'text-red-700 bg-red-50',
    };
    return map[action] ?? 'text-gray-600 bg-gray-50';
};

const auditActionLabel = (action: string) => {
    const map: Record<string, string> = {
        created: t('admin.dashboard.audit_action_created'),
        updated: t('admin.dashboard.audit_action_updated'),
        deleted: t('admin.dashboard.audit_action_deleted'),
    };
    return map[action] ?? action;
};

const formatModelType = (type: string) => {
    const model = type.replace('App\\Models\\', '');
    return t(`admin.dashboard.audit_model_${model}`, model);
};

const formatDate = (dateStr: string) =>
    new Date(dateStr).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });
</script>

<template>
    <Head title="Pārvaldības panelis" />
    <AdminLayout>
        <div class="bg-navy">
            <div class="h-1 bg-gold" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ t('admin.dashboard.title') }}</h1>
                <p class="text-white/50 text-sm mt-1">{{ t('admin.dashboard.subtitle') }}</p>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

            <!-- Statistikas kartes -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">{{ t('admin.dashboard.section_stats') }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                    <component
                        :is="card.href ? Link : 'div'"
                        v-for="card in statCards"
                        :key="card.label"
                        :href="card.href"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-col gap-2 transition-all"
                        :class="card.href ? 'hover:shadow-md hover:border-gray-200 hover:-translate-y-0.5 cursor-pointer' : ''"
                    >
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center" :class="card.bg">
                            <component :is="card.icon" class="w-4 h-4" :class="card.iconColor" />
                        </div>
                        <div>
                            <p class="text-xl font-extrabold text-gray-900 leading-none">{{ card.value }}</p>
                            <p class="text-[11px] font-medium text-gray-400 mt-0.5 leading-snug">{{ card.label }}</p>
                        </div>
                    </component>
                </div>
            </div>

            <!-- Grafiki -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">{{ t('admin.dashboard.section_charts') }}</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Darbu sadalījums pa statusiem -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-5">{{ t('admin.dashboard.chart_jobs_by_status') }}</h3>
                        <div class="flex items-center gap-6">
                            <svg viewBox="0 0 100 100" class="w-36 h-36 shrink-0">
                                <path
                                    v-for="seg in donutSegments"
                                    :key="seg.status"
                                    :d="donutPath(seg.pct, seg.offset)"
                                    :fill="jobStatusConfig[seg.status]?.color ?? '#9ca3af'"
                                />
                                <circle cx="50" cy="50" r="24" fill="white" />
                                <text x="50" y="47" text-anchor="middle" class="text-xs" font-size="8" fill="#6b7280">{{ t('admin.dashboard.total') }}</text>
                                <text x="50" y="57" text-anchor="middle" font-size="10" font-weight="bold" fill="#111827">{{ totalJobs }}</text>
                            </svg>
                            <div class="flex-1 space-y-1.5">
                                <div
                                    v-for="([status, count]) in Object.entries(jobsByStatus).filter(([,c]) => c > 0)"
                                    :key="status"
                                    class="flex items-center justify-between gap-2"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ background: jobStatusConfig[status]?.color ?? '#9ca3af' }" />
                                        <span class="text-xs text-gray-600 truncate">{{ jobStatusConfig[status]?.label ?? status }}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-900 shrink-0">{{ count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Darbu dinamika - pēdējās 8 nedēļas -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-5">{{ t('admin.dashboard.chart_jobs_over_time') }}</h3>
                        <div class="flex items-end gap-2 h-32">
                            <div
                                v-for="week in jobsOverTime"
                                :key="week.week"
                                class="flex-1 flex flex-col items-center gap-1"
                            >
                                <span class="text-[10px] text-gray-400 font-medium">{{ week.count || '' }}</span>
                                <div
                                    class="w-full rounded-t-md transition-all"
                                    :style="{
                                        height: week.count ? `${Math.max(4, (week.count / maxWeekCount) * 96)}px` : '4px',
                                        background: week.count ? '#1e3a5f' : '#e5e7eb',
                                    }"
                                />
                                <span class="text-[10px] text-gray-400">{{ weekLabel(week.week) }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Nepieciešama uzmanība -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">{{ t('admin.dashboard.section_attention') }}</h2>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div
                        v-for="item in [
                            {
                                key: 'complaints',
                                count: needsAttention.pendingComplaints,
                                label: t('admin.dashboard.attention_complaints'),
                                href: route('admin.complaints.index'),
                                urgent: needsAttention.pendingComplaints > 0,
                            },
                            {
                                key: 'disputes',
                                count: needsAttention.activeDisputes,
                                label: t('admin.dashboard.attention_disputes'),
                                href: route('admin.job-requests.index'),
                                urgent: needsAttention.activeDisputes > 0,
                            },
                            {
                                key: 'suggestions',
                                count: needsAttention.pendingSuggestions,
                                label: t('admin.dashboard.attention_suggestions'),
                                href: route('admin.category-suggestions.index'),
                                urgent: needsAttention.pendingSuggestions > 0,
                            },
                        ]"
                        :key="item.key"
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-50 last:border-b-0"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold"
                                :class="item.urgent ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-400'"
                            >
                                {{ item.count }}
                            </span>
                            <span class="text-sm font-medium" :class="item.urgent ? 'text-gray-900' : 'text-gray-400'">
                                {{ item.label }}
                            </span>
                        </div>
                        <Link
                            :href="item.href"
                            class="text-xs font-semibold transition-colors"
                            :class="item.urgent ? 'text-navy hover:underline' : 'text-gray-300 pointer-events-none'"
                        >
                            {{ item.urgent ? t('admin.dashboard.view') : t('admin.dashboard.all_clear') }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Pēdējā aktivitāte -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">{{ t('admin.dashboard.section_recent') }}</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
                            <h3 class="text-sm font-bold text-gray-900">{{ t('admin.dashboard.recent_complaints') }}</h3>
                            <Link :href="route('admin.complaints.index')" class="text-xs font-medium text-navy hover:underline">
                                {{ t('admin.dashboard.view_all') }}
                            </Link>
                        </div>
                        <div v-if="recentComplaints.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                            {{ t('admin.dashboard.no_complaints') }}
                        </div>
                        <ul v-else class="divide-y divide-gray-50">
                            <li
                                v-for="complaint in recentComplaints"
                                :key="complaint.id"
                                class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50/60 transition-colors"
                            >
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ complaint.reporter.name }}
                                        <span class="text-gray-400 font-normal mx-1">→</span>
                                        {{ complaint.reported_user.name }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(complaint.created_at) }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shrink-0"
                                    :class="complaintStatusBadge(complaint.status)">
                                    {{ complaintStatusLabel(complaint.status) }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
                            <h3 class="text-sm font-bold text-gray-900">{{ t('admin.dashboard.recent_audit') }}</h3>
                            <Link :href="route('admin.audit-logs.index')" class="text-xs font-medium text-navy hover:underline">
                                {{ t('admin.dashboard.view_all') }}
                            </Link>
                        </div>
                        <div v-if="recentAuditLogs.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                            {{ t('admin.dashboard.no_audit') }}
                        </div>
                        <ul v-else class="divide-y divide-gray-50">
                            <li
                                v-for="log in recentAuditLogs"
                                :key="log.id"
                                class="px-6 py-3 flex items-center gap-3 hover:bg-gray-50/60 transition-colors"
                            >
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-bold shrink-0" :class="auditActionBadge(log.action)">
                                    {{ auditActionLabel(log.action) }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 truncate">
                                        {{ formatModelType(log.auditable_type) }}
                                        <span class="text-gray-400"> · {{ log.user ? log.user.name : 'Sistēma' }}</span>
                                    </p>
                                </div>
                                <span class="text-xs text-gray-400 shrink-0">{{ formatDate(log.created_at) }}</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </AdminLayout>
</template>
