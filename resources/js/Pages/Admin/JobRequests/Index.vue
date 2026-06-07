<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import {
    EyeIcon,
    TrashIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';

interface JobRequest {
    id: number;
    title: string;
    budget: number | null;
    status: string;
    created_at: string;
    category: { name: string } | null;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
}

interface PaginatedJobRequests {
    data: JobRequest[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

const props = defineProps<{
    jobRequests: PaginatedJobRequests;
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const doSearch = () => {
    router.get(route('admin.job-requests.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    router.get(route('admin.job-requests.index'), {}, { preserveState: true, replace: true });
};

const hasFilters = () => !!props.filters.search || !!props.filters.status;

const deleteTarget = ref<JobRequest | null>(null);
const deleteProcessing = ref(false);

const doDelete = () => {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(route('admin.job-requests.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteTarget.value = null;
        },
    });
};

const { t } = useI18n();

const statusLabel = (s: string) => ({
    open:                  t('admin.dashboard.status_open'),
    accepted:              t('admin.dashboard.status_accepted'),
    in_progress:           t('admin.dashboard.status_in_progress'),
    awaiting_confirmation: t('admin.dashboard.status_awaiting'),
    completed:             t('admin.dashboard.status_completed'),
    disputed:              t('admin.dashboard.status_disputed'),
    cancelled:             t('admin.dashboard.status_cancelled'),
}[s] ?? s);

const statusClass = (s: string) => ({
    open:                  'bg-emerald-100 text-emerald-700',
    accepted:              'bg-blue-100 text-blue-700',
    in_progress:           'bg-amber-100 text-amber-700',
    awaiting_confirmation: 'bg-orange-100 text-orange-700',
    completed:             'bg-indigo-100 text-indigo-700',
    disputed:              'bg-red-100 text-red-700',
    cancelled:             'bg-gray-100 text-gray-500',
}[s] ?? 'bg-gray-100 text-gray-500');

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });
</script>

<template>
    <AdminLayout>
        
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Sludinājumi</h1>
                <p class="text-white/50 text-sm mt-1">Visi sistēmas sludinājumi ({{ jobRequests.total }})</p>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 max-w-sm">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="search"
                        @keydown.enter="doSearch"
                        type="text"
                        placeholder="Meklēt pēc nosaukuma..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                    />
                </div>

                <select
                    v-model="status"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                >
                    <option value="">Visi statusi</option>
                    <option value="active">Aktīvs</option>
                    <option value="assigned">Piešķirts</option>
                    <option value="completed">Pabeigts</option>
                    <option value="cancelled">Atcelts</option>
                </select>

                <button
                    @click="doSearch"
                    class="px-4 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy/90 transition-colors"
                >
                    Meklēt
                </button>

                <button
                    v-if="hasFilters()"
                    @click="clearFilters"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Notīrīt
                </button>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nosaukums</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Kategorija</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Budžets</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statuss</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Izveidots</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Darbības</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="jobRequests.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">
                                Nav atrasts neviens sludinājums.
                            </td>
                        </tr>
                        <tr
                            v-for="job in jobRequests.data"
                            :key="job.id"
                            class="hover:bg-gray-50/60 transition-colors"
                        >
                            
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ job.title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ job.user?.name }} · {{ job.user?.email }}</p>
                            </td>

                            
                            <td class="px-6 py-4 text-gray-600 hidden md:table-cell">
                                {{ job.category?.name ?? '-' }}
                            </td>

                            
                            <td class="px-6 py-4 text-gray-700 hidden md:table-cell">
                                <span v-if="job.budget">€{{ job.budget }}</span>
                                <span v-else class="text-gray-400">-</span>
                            </td>

                            
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="statusClass(job.status)"
                                >
                                    {{ statusLabel(job.status) }}
                                </span>
                            </td>

                            
                            <td class="px-6 py-4 text-gray-500 hidden lg:table-cell">
                                {{ formatDate(job.created_at) }}
                            </td>

                            
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <Link
                                        :href="route('admin.job-requests.show', job.id)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-navy hover:bg-gray-100 transition-colors"
                                        title="Skatīt"
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                    </Link>
                                    <button
                                        @click="deleteTarget = job"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Dzēst"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            
            <div v-if="jobRequests.last_page > 1" class="flex items-center justify-between text-sm text-gray-500">
                <p>Rāda {{ jobRequests.from }}-{{ jobRequests.to }} no {{ jobRequests.total }}</p>
                <div class="flex items-center gap-1">
                    <template v-for="link in jobRequests.links" :key="link.label">
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

        
        <ConfirmDialog
            :show="!!deleteTarget"
            title="Dzēst sludinājumu?"
            :message="`Vai tiešām vēlaties dzēst sludinājumu &quot;${deleteTarget?.title}&quot;? Šo darbību nevar atcelt.`"
            confirmLabel="Dzēst"
            :processing="deleteProcessing"
            @confirm="doDelete"
            @cancel="deleteTarget = null"
        />
    </AdminLayout>
</template>
