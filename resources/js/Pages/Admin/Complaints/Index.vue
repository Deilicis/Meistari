<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { EyeIcon, ShieldExclamationIcon } from '@heroicons/vue/24/outline';

interface ComplaintUser {
    id: number;
    name: string;
}

interface Complaint {
    id: number;
    reporter: ComplaintUser;
    reported_user: ComplaintUser;
    reported_entity_type: string | null;
    reason: string;
    status: string;
    created_at: string;
}

interface PaginatedComplaints {
    data: Complaint[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

const props = defineProps<{
    complaints: PaginatedComplaints;
    filters: { status?: string };
    statuses: { name: string; value: string }[];
}>();

type StatusKey = 'pending' | 'reviewed' | 'resolved' | 'dismissed';

const statusConfig: Record<StatusKey, { label: string; classes: string }> = {
    pending:   { label: 'Gaida',       classes: 'bg-amber-100 text-amber-800 ring-1 ring-amber-200' },
    reviewed:  { label: 'Izskatīts',   classes: 'bg-blue-100 text-blue-800 ring-1 ring-blue-200' },
    resolved:  { label: 'Atrisināts',  classes: 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200' },
    dismissed: { label: 'Noraidīts',   classes: 'bg-gray-100 text-gray-600 ring-1 ring-gray-200' },
};

const tabs = [
    { label: 'Visi',       value: '' },
    { label: 'Gaida',      value: 'pending' },
    { label: 'Izskatīts',  value: 'reviewed' },
    { label: 'Atrisināts', value: 'resolved' },
    { label: 'Noraidīts',  value: 'dismissed' },
];

const setStatus = (value: string) => {
    router.get(route('admin.complaints.index'), value ? { status: value } : {}, { preserveState: false });
};

const entityLabel = (type: string | null): string => {
    if (!type) return 'Lietotājs';
    if (type.includes('Service')) return 'Pakalpojums';
    if (type.includes('JobRequest')) return 'Sludinājums';
    return 'Entitija';
};

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });
</script>

<template>
    <AdminLayout>
        <div class="bg-navy">
            <div class="h-1 bg-rose-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <ShieldExclamationIcon class="w-6 h-6 text-rose-400" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">Sūdzības</h1>
                        <p class="text-white/50 text-sm mt-0.5">
                            Kopā <span class="text-rose-400 font-semibold">{{ complaints.total }}</span> sūdzīb{{ complaints.total === 1 ? 'a' : 'as' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            
            <div class="flex items-center gap-2 flex-wrap">
                <button
                    v-for="tab in tabs"
                    :key="tab.value"
                    @click="setStatus(tab.value)"
                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition-colors"
                    :class="(filters.status ?? '') === tab.value
                        ? 'bg-navy text-white'
                        : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                >
                    {{ tab.label }}
                </button>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Iesniedzējs</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Par lietotāju</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Par ko</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Iemesls</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statuss</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Datums</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Darbības</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="complaints.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">
                                Nav nevienas sūdzības.
                            </td>
                        </tr>
                        <tr
                            v-for="complaint in complaints.data"
                            :key="complaint.id"
                            class="hover:bg-gray-50/60 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ complaint.reporter.name }}</p>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <p class="text-gray-700">{{ complaint.reported_user.name }}</p>
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ entityLabel(complaint.reported_entity_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell max-w-xs">
                                <p class="text-gray-600 truncate">{{ complaint.reason.slice(0, 80) }}{{ complaint.reason.length > 80 ? '...' : '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="statusConfig[complaint.status as StatusKey]?.classes ?? statusConfig.pending.classes"
                                >
                                    {{ statusConfig[complaint.status as StatusKey]?.label ?? complaint.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 hidden md:table-cell">
                                {{ formatDate(complaint.created_at) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end">
                                    <Link
                                        :href="route('admin.complaints.show', complaint.id)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-navy hover:bg-gray-100 transition-colors"
                                        title="Skatīt"
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            
            <div v-if="complaints.last_page > 1" class="flex items-center justify-between text-sm text-gray-500">
                <p>Rāda {{ complaints.from }}-{{ complaints.to }} no {{ complaints.total }}</p>
                <div class="flex items-center gap-1">
                    <template v-for="link in complaints.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1.5 rounded-lg border transition-colors"
                            :class="link.active
                                ? 'bg-navy text-white border-navy font-semibold'
                                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 bg-white cursor-default"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
