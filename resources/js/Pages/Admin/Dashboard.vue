<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    UserGroupIcon,
    WrenchScrewdriverIcon,
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    ExclamationTriangleIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

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
        seekers: number;
        masters: number;
        moderators: number;
        services: number;
        jobRequests: number;
        pendingComplaints: number;
    };
    recentComplaints: RecentComplaint[];
    recentAuditLogs: RecentAuditLog[];
}>();

const statCards = computed(() => [
    {
        label: 'Meklētāji',
        value: props.stats.seekers,
        icon: UserGroupIcon,
        bg: 'bg-blue-200',
        iconColor: 'text-blue-700',
    },
    {
        label: 'Meistari',
        value: props.stats.masters,
        icon: WrenchScrewdriverIcon,
        bg: 'bg-amber-200',
        iconColor: 'text-amber-700',
    },
    {
        label: 'Moderatori',
        value: props.stats.moderators,
        icon: ShieldCheckIcon,
        bg: 'bg-orange-200',
        iconColor: 'text-orange-700',
    },
    {
        label: 'Pakalpojumi',
        value: props.stats.services,
        icon: BriefcaseIcon,
        bg: 'bg-emerald-200',
        iconColor: 'text-emerald-700',
    },
    {
        label: 'Sludinājumi',
        value: props.stats.jobRequests,
        icon: ClipboardDocumentListIcon,
        bg: 'bg-purple-200',
        iconColor: 'text-purple-700',
    },
    {
        label: 'Gaidošās sūdzības',
        value: props.stats.pendingComplaints,
        icon: ExclamationTriangleIcon,
        bg: props.stats.pendingComplaints > 0 ? 'bg-red-200' : 'bg-gray-200',
        iconColor: props.stats.pendingComplaints > 0 ? 'text-red-700' : 'text-gray-500',
    },
]);

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
        pending: 'Gaida',
        reviewed: 'Izskatīta',
        resolved: 'Atrisināta',
        dismissed: 'Noraidīta',
    };
    return map[status] ?? status;
};

const formatModelType = (type: string) => type.replace('App\\Models\\', '');

const formatDate = (dateStr: string) =>
    new Date(dateStr).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });
</script>

<template>
    <AdminLayout>
        <!-- Greeting bar -->
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Pārvaldības Panelis</h1>
                <p class="text-white/50 text-sm mt-1">Sistēmas pārskats un pēdējā aktivitāte</p>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

            <!-- Stat cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div
                    v-for="card in statCards"
                    :key="card.label"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3"
                >
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center" :class="card.bg">
                        <component :is="card.icon" class="w-5 h-5" :class="card.iconColor" />
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-gray-900">{{ card.value }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ card.label }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Recent Complaints -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
                        <h2 class="text-sm font-bold text-gray-900">Pēdējās sūdzības</h2>
                        <Link
                            :href="route('admin.complaints.index')"
                            class="text-xs font-medium text-navy hover:underline"
                        >
                            Skatīt visas
                        </Link>
                    </div>

                    <div v-if="recentComplaints.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                        Nav sūdzību.
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

                <!-- Recent Audit Logs -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
                        <h2 class="text-sm font-bold text-gray-900">Pēdējā aktivitāte</h2>
                        <Link
                            :href="route('admin.audit-logs.index')"
                            class="text-xs font-medium text-navy hover:underline"
                        >
                            Skatīt visus
                        </Link>
                    </div>

                    <div v-if="recentAuditLogs.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                        Nav aktivitātes.
                    </div>

                    <ul v-else class="divide-y divide-gray-50">
                        <li
                            v-for="log in recentAuditLogs"
                            :key="log.id"
                            class="px-6 py-3 flex items-center justify-between gap-4 hover:bg-gray-50/60 transition-colors"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <span class="capitalize">{{ log.action }}</span>
                                    <span class="text-gray-400 font-normal"> — </span>
                                    <span class="text-gray-600">{{ formatModelType(log.auditable_type) }}</span>
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ log.user ? log.user.name : 'Sistēma' }}
                                </p>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0">{{ formatDate(log.created_at) }}</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
