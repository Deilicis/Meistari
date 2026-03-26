<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import type { ApplicationWithJobRequest, ApplicationStatus } from '@/types/models';

const props = defineProps<{
    applications: ApplicationWithJobRequest[];
}>();

const activeFilter = ref<ApplicationStatus | 'all'>('all');

const statusConfig: Record<ApplicationStatus, { label: string; badgeClass: string }> = {
    pending:   { label: 'Gaida apstiprinājumu', badgeClass: 'bg-amber-100 text-amber-800' },
    accepted:  { label: 'Pieņemts', badgeClass: 'bg-green-100 text-green-800' },
    rejected:  { label: 'Noraidīts', badgeClass: 'bg-red-100 text-red-800' },
    completed: { label: 'Pabeigts', badgeClass: 'bg-blue-100 text-blue-800' },
    cancelled: { label: 'Atcelts', badgeClass: 'bg-gray-100 text-gray-600' },
};

const tabs: { key: ApplicationStatus | 'all'; label: string }[] = [
    { key: 'all', label: 'Visi' },
    { key: 'pending', label: 'Gaida' },
    { key: 'accepted', label: 'Pieņemti' },
    { key: 'rejected', label: 'Noraidīti' },
    { key: 'cancelled', label: 'Atcelti' },
];

const filteredApplications = computed(() => {
    if (activeFilter.value === 'all') return props.applications;
    return props.applications.filter(a => a.status === activeFilter.value);
});

const tabCount = (key: ApplicationStatus | 'all') => {
    if (key === 'all') return props.applications.length;
    return props.applications.filter(a => a.status === key).length;
};

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('lv-LV', { year: 'numeric', month: 'short', day: 'numeric' });

const seekerName = (app: ApplicationWithJobRequest): string => {
    const profile = app.job_request?.user?.profile;
    const user = app.job_request?.user;
    if (!profile) return user?.name ?? '—';
    if (profile.type === 'company') return profile.company_name ?? user?.name ?? '—';
    const parts = [profile.first_name, profile.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : (user?.name ?? '—');
};

const cancelTarget = ref<ApplicationWithJobRequest | null>(null);
const cancelling = ref(false);

const startCancel = (app: ApplicationWithJobRequest) => { cancelTarget.value = app; };
const cancelDelete = () => { cancelTarget.value = null; };

const confirmCancel = async () => {
    if (!cancelTarget.value) return;
    cancelling.value = true;
    try {
        await axios.delete(route('api.applications.destroy', cancelTarget.value.id));
        toast.success('Pieteikums atcelts.');
        cancelTarget.value = null;
        router.reload({ only: ['applications'] });
    } catch {
        toast.error('Neizdevās atcelt pieteikumu.');
    } finally {
        cancelling.value = false;
    }
};
</script>

<template>
    <Head title="Mani Pieteikumi" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

                <div class="flex items-center gap-3 mb-6">
                    <h1 class="text-2xl font-bold text-navy">Mani Pieteikumi</h1>
                    <span class="text-sm font-semibold bg-navy/10 text-navy px-2.5 py-0.5 rounded-full">
                        {{ applications.length }}
                    </span>
                </div>

                <div class="flex gap-1 flex-wrap mb-6 border-b border-gray-200">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeFilter = tab.key"
                        class="px-3 py-2 text-sm font-medium rounded-t-md transition-colors relative -mb-px"
                        :class="activeFilter === tab.key
                            ? 'text-navy border-b-2 border-navy bg-navy/5'
                            : 'text-gray-500 hover:text-navy hover:bg-gray-50'"
                    >
                        {{ tab.label }}
                        <span v-if="tabCount(tab.key) > 0"
                            class="ml-1.5 text-xs font-bold px-1.5 py-0.5 rounded-full"
                            :class="activeFilter === tab.key ? 'bg-navy text-white' : 'bg-gray-200 text-gray-600'">
                            {{ tabCount(tab.key) }}
                        </span>
                    </button>
                </div>

                <div v-if="filteredApplications.length > 0" class="space-y-3">
                    <div
                        v-for="app in filteredApplications"
                        :key="app.id"
                        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <p class="font-semibold text-navy text-base leading-snug">
                                        {{ app.job_request?.title ?? '—' }}
                                    </p>
                                    <span v-if="app.job_request?.category"
                                        class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                        {{ app.job_request.category.name }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-500 mb-3">
                                    Pasūtītājs: <span class="font-medium text-gray-700">{{ seekerName(app) }}</span>
                                    <span v-if="app.job_request?.user?.profile?.city" class="text-gray-400">
                                        · {{ app.job_request.user.profile.city }}
                                    </span>
                                </p>

                                <p v-if="app.cover_letter" class="text-sm text-gray-600 line-clamp-2 mb-3">
                                    {{ app.cover_letter }}
                                </p>

                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span v-if="app.job_request?.budget !== null && app.job_request?.budget !== undefined">
                                        Budžets: <span class="font-semibold text-gray-800">€{{ app.job_request.budget }}</span>
                                    </span>
                                    <span v-if="app.price_offer !== null" class="flex items-center gap-1">
                                        <span class="text-gray-400">Mans piedāvājums:</span>
                                        <span class="font-semibold text-emerald-700">€{{ app.price_offer }}</span>
                                    </span>
                                    <span v-else class="text-gray-400 italic">—</span>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-2 shrink-0">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                    :class="statusConfig[app.status].badgeClass">
                                    {{ statusConfig[app.status].label }}
                                </span>
                                <span class="text-xs text-gray-400">{{ formatDate(app.created_at) }}</span>
                                <button
                                    v-if="app.status === 'pending'"
                                    @click="startCancel(app)"
                                    class="text-xs font-semibold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 hover:border-red-300 rounded-md px-2.5 py-1 transition-colors"
                                >
                                    Atcelt pieteikumu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <EmptyState
                    v-else
                    :title="activeFilter === 'all' ? 'Vēl nav neviena pieteikuma' : 'Nav pieteikumu šajā kategorijā'"
                    :description="activeFilter === 'all' ? 'Atrodi darba sludinājumus un piesakies!' : 'Izmēģini citu filtru.'"
                >
                    <template #icon>
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                    </template>
                </EmptyState>

            </div>
        </div>
    </AuthenticatedLayout>

    <ConfirmDialog
        :show="cancelTarget !== null"
        title="Atcelt pieteikumu?"
        :message="cancelTarget ? `Vai tiešām vēlaties atcelt pieteikumu sludinājumam &quot;${cancelTarget.job_request?.title ?? ''}&quot;? Šo darbību nevar atsaukt.` : ''"
        confirmLabel="Atcelt pieteikumu"
        :processing="cancelling"
        @confirm="confirmCancel"
        @cancel="cancelDelete"
    />
</template>
