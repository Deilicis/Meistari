<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import {
    ArrowLeftIcon,
    EnvelopeIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';

interface Application {
    id: number;
    cover_letter: string | null;
    price_offer: number | null;
    status: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
        profile: { city: string | null; avatar: string | null } | null;
    };
}

interface JobRequest {
    id: number;
    title: string;
    description: string;
    budget: number | null;
    deadline: string | null;
    location: string[] | null;
    status: string;
    created_at: string;
    updated_at: string;
    category: { name: string } | null;
    user: {
        id: number;
        name: string;
        email: string;
        profile: { city: string | null } | null;
    };
    applications: Application[];
}

defineProps<{ jobRequest: JobRequest }>();

const showDeleteConfirm = ref(false);
const deleteProcessing = ref(false);

const doDelete = (jobId: number) => {
    deleteProcessing.value = true;
    router.delete(route('admin.job-requests.destroy', jobId), {
        onFinish: () => {
            deleteProcessing.value = false;
            showDeleteConfirm.value = false;
        },
    });
};

const statusLabel = (s: string) => ({
    active: 'Aktīvs',
    assigned: 'Piešķirts',
    completed: 'Pabeigts',
    cancelled: 'Atcelts',
}[s] ?? s);

const statusClass = (s: string) => ({
    active: 'bg-emerald-100 text-emerald-700',
    assigned: 'bg-blue-100 text-blue-700',
    completed: 'bg-gray-100 text-gray-600',
    cancelled: 'bg-red-100 text-red-600',
}[s] ?? 'bg-gray-100 text-gray-600');

const statusHeaderClass = (s: string) => ({
    active: 'bg-emerald-400/20 text-emerald-300',
    assigned: 'bg-blue-400/20 text-blue-300',
    completed: 'bg-white/10 text-white/50',
    cancelled: 'bg-red-400/20 text-red-300',
}[s] ?? 'bg-white/10 text-white/50');

const appStatusLabel = (s: string) => ({
    pending: 'Gaida',
    accepted: 'Pieņemts',
    rejected: 'Noraidīts',
    completed: 'Pabeigts',
    cancelled: 'Atcelts',
}[s] ?? s);

const appStatusClass = (s: string) => ({
    pending: 'bg-amber-100 text-amber-700',
    accepted: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-600',
    completed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-gray-100 text-gray-500',
}[s] ?? 'bg-gray-100 text-gray-600');

const truncate = (text: string | null, len = 100) => {
    if (!text) return '—';
    return text.length > len ? text.slice(0, len) + '…' : text;
};

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });

const formatDateTime = (d: string) =>
    new Date(d).toLocaleString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <AdminLayout>
        <!-- Header -->
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <Link
                    :href="route('admin.job-requests.index')"
                    class="inline-flex items-center gap-1.5 text-white/50 hover:text-white text-sm mb-4 transition-colors"
                >
                    <ArrowLeftIcon class="w-4 h-4" />
                    Sludinājumi
                </Link>
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ jobRequest.title }}</h1>
                            <span
                                class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                :class="statusHeaderClass(jobRequest.status)"
                            >
                                {{ statusLabel(jobRequest.status) }}
                            </span>
                        </div>
                        <p class="text-white/50 text-sm mt-1">{{ jobRequest.category?.name ?? '—' }} · #{{ jobRequest.id }}</p>
                    </div>
                    <button
                        @click="showDeleteConfirm = true"
                        class="px-4 py-2 text-sm font-semibold text-red-400 border border-red-400/30 rounded-lg hover:bg-red-400/10 transition-colors"
                    >
                        Dzēst
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Details -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-navy px-6 py-4">
                        <h2 class="text-sm font-bold text-white">Detaļas</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-xs text-gray-400">Kategorija</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">{{ jobRequest.category?.name ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400">Budžets</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">
                                <span v-if="jobRequest.budget">€{{ jobRequest.budget }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400">Termiņš</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">
                                <span v-if="jobRequest.deadline">{{ formatDateTime(jobRequest.deadline) }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </p>
                        </div>

                        <div v-if="jobRequest.location?.length">
                            <p class="text-xs text-gray-400">Atrašanās vieta</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">{{ jobRequest.location.join(', ') }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400">Statuss</p>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold mt-0.5"
                                :class="statusClass(jobRequest.status)"
                            >
                                {{ statusLabel(jobRequest.status) }}
                            </span>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400">Izveidots</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">{{ formatDate(jobRequest.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Seeker card -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-navy px-6 py-4">
                        <h2 class="text-sm font-bold text-white">Pieteicējs</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-navy/10 flex items-center justify-center text-lg font-extrabold text-navy shrink-0">
                                {{ jobRequest.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="route('admin.seekers.show', jobRequest.user.id)"
                                    class="text-base font-semibold text-navy hover:underline"
                                >
                                    {{ jobRequest.user.name }}
                                </Link>
                                <div class="flex items-center gap-3 mt-1 flex-wrap">
                                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <EnvelopeIcon class="w-3.5 h-3.5 text-gray-400" />
                                        {{ jobRequest.user.email }}
                                    </span>
                                    <span v-if="jobRequest.user.profile?.city" class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <MapPinIcon class="w-3.5 h-3.5 text-gray-400" />
                                        {{ jobRequest.user.profile.city }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-navy px-6 py-4">
                    <h2 class="text-sm font-bold text-white">Apraksts</h2>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ jobRequest.description }}</p>
                </div>
            </div>

            <!-- Applications -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-navy px-6 py-4">
                    <h2 class="text-sm font-bold text-white">
                        Pieteikumi ({{ jobRequest.applications.length }})
                    </h2>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Meistars</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Piedāvājums</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Vēstule</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statuss</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Datums</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="jobRequest.applications.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">
                                Nav pieteikumu.
                            </td>
                        </tr>
                        <tr
                            v-for="app in jobRequest.applications"
                            :key="app.id"
                            class="hover:bg-gray-50/60 transition-colors"
                        >
                            <!-- Master -->
                            <td class="px-6 py-4">
                                <Link
                                    :href="route('admin.masters.show', app.user.id)"
                                    class="font-semibold text-navy hover:underline"
                                >
                                    {{ app.user.name }}
                                </Link>
                                <p class="text-xs text-gray-400 mt-0.5">{{ app.user.email }}</p>
                            </td>

                            <!-- Price offer -->
                            <td class="px-6 py-4 text-gray-700 hidden md:table-cell">
                                <span v-if="app.price_offer">€{{ app.price_offer }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </td>

                            <!-- Cover letter -->
                            <td class="px-6 py-4 text-gray-500 hidden lg:table-cell max-w-xs">
                                {{ truncate(app.cover_letter) }}
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="appStatusClass(app.status)"
                                >
                                    {{ appStatusLabel(app.status) }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-6 py-4 text-gray-500 hidden md:table-cell whitespace-nowrap">
                                {{ formatDate(app.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Delete confirm -->
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="Dzēst sludinājumu?"
            :message="`Vai tiešām vēlaties dzēst sludinājumu &quot;${jobRequest.title}&quot;? Šo darbību nevar atcelt.`"
            confirmLabel="Dzēst"
            :processing="deleteProcessing"
            @confirm="doDelete(jobRequest.id)"
            @cancel="showDeleteConfirm = false"
        />
    </AdminLayout>
</template>
