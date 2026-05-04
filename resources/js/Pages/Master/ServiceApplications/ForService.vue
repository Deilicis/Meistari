<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { MasterServiceApplication, ServiceApplicationStatus } from '@/types/serviceApplication';
import { InboxArrowDownIcon, CheckCircleIcon, XCircleIcon, ChatBubbleLeftIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    applications: { data: MasterServiceApplication[] };
    serviceId: number;
}>();

const applications = ref<MasterServiceApplication[]>(props.applications.data ?? []);
const activeFilter = ref<ServiceApplicationStatus | 'all'>('all');
const loading = ref<number | null>(null);

const serviceTitle = computed(() => props.applications.data[0]?.service.title ?? '');

const statusConfig: Record<ServiceApplicationStatus, { label: string; badgeClass: string; borderClass: string }> = {
    pending:   { label: 'Gaida atbildi',  badgeClass: 'bg-amber-100 text-amber-700',    borderClass: 'border-l-amber-400' },
    accepted:  { label: 'Pieņemts',       badgeClass: 'bg-emerald-100 text-emerald-700', borderClass: 'border-l-emerald-500' },
    rejected:  { label: 'Noraidīts',      badgeClass: 'bg-red-100 text-red-700',         borderClass: 'border-l-red-400' },
    completed: { label: 'Pabeigts',       badgeClass: 'bg-blue-100 text-blue-700',       borderClass: 'border-l-blue-400' },
    cancelled: { label: 'Atcelts',        badgeClass: 'bg-gray-100 text-gray-500',       borderClass: 'border-l-gray-300' },
};

const tabs: { key: ServiceApplicationStatus | 'all'; label: string }[] = [
    { key: 'all',      label: 'Visi' },
    { key: 'pending',  label: 'Gaida atbildi' },
    { key: 'accepted', label: 'Pieņemti' },
    { key: 'rejected', label: 'Noraidīti' },
];

const filtered = computed(() => {
    if (activeFilter.value === 'all') return applications.value;
    return applications.value.filter(a => a.status === activeFilter.value);
});

const tabCount = (key: ServiceApplicationStatus | 'all') => {
    if (key === 'all') return applications.value.length;
    return applications.value.filter(a => a.status === key).length;
};

const formatDate = (iso: string) =>
    new Date(iso).toLocaleString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

async function accept(app: MasterServiceApplication) {
    if (loading.value) return;
    loading.value = app.id;
    try {
        const { data } = await axios.patch(route('api.master.service-applications.accept', app.id));
        const idx = applications.value.findIndex(a => a.id === app.id);
        if (idx !== -1) applications.value[idx] = data;
        toast.success('Pieteikums pieņemts!');
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? 'Radās kļūda.');
    } finally {
        loading.value = null;
    }
}

async function reject(app: MasterServiceApplication) {
    if (loading.value) return;
    loading.value = app.id;
    try {
        const { data } = await axios.patch(route('api.master.service-applications.reject', app.id));
        const idx = applications.value.findIndex(a => a.id === app.id);
        if (idx !== -1) applications.value[idx] = data;
        toast.success('Pieteikums noraidīts.');
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? 'Radās kļūda.');
    } finally {
        loading.value = null;
    }
}
</script>

<template>
    <Head :title="serviceTitle ? `Pieteikumi — ${serviceTitle}` : 'Pieteikumi pakalpojumam'" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1 bg-gold" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <Link
                    :href="route('master.service-applications.index')"
                    class="inline-flex items-center gap-1 text-sm text-white/50 hover:text-white transition-colors mb-3"
                >
                    <ArrowLeftIcon class="w-4 h-4" />
                    Visi pieteikumi
                </Link>
                <div class="flex items-center gap-3">
                    <InboxArrowDownIcon class="w-6 h-6 text-gold" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">
                            {{ serviceTitle || 'Pieteikumi pakalpojumam' }}
                        </h1>
                        <p class="text-white/50 text-sm mt-0.5">
                            <span class="text-gold font-semibold">{{ applications.length }}</span>
                            pieteikum{{ applications.length === 1 ? 's' : 'i' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Filter tabs -->
                <div class="flex flex-wrap gap-2 mb-5 px-4 sm:px-0">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeFilter = tab.key"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold transition-colors"
                        :class="activeFilter === tab.key
                            ? 'bg-navy text-white'
                            : 'bg-white text-gray-500 border border-gray-200 hover:border-navy/30 hover:text-navy'"
                    >
                        {{ tab.label }}
                        <span
                            class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[11px] font-bold"
                            :class="activeFilter === tab.key ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'"
                        >
                            {{ tabCount(tab.key) }}
                        </span>
                    </button>
                </div>

                <!-- Empty state -->
                <div v-if="filtered.length === 0" class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
                    <InboxArrowDownIcon class="w-10 h-10 text-gray-200 mx-auto mb-3" />
                    <h3 class="text-base font-semibold text-gray-900 mb-1">Nav pieteikumu</h3>
                    <p class="text-sm text-gray-400">Šim pakalpojumam vēl nav pieteikumu šajā kategorijā.</p>
                </div>

                <!-- Application cards -->
                <div v-else class="space-y-3">
                    <div
                        v-for="app in filtered"
                        :key="app.id"
                        class="bg-white rounded-xl border border-gray-100 shadow-sm border-l-4 px-5 py-4"
                        :class="statusConfig[app.status].borderClass"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Avatar -->
                            <div class="shrink-0">
                                <img
                                    v-if="app.applicant.avatar_url"
                                    :src="app.applicant.avatar_url"
                                    :alt="app.applicant.name"
                                    class="w-10 h-10 rounded-full object-cover border border-gray-100"
                                />
                                <div
                                    v-else
                                    class="w-10 h-10 rounded-full bg-navy/10 flex items-center justify-center text-sm font-bold text-navy"
                                >
                                    {{ app.applicant.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 flex-wrap">
                                    <p class="text-sm font-bold text-navy">{{ app.applicant.name }}</p>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold shrink-0"
                                        :class="statusConfig[app.status].badgeClass"
                                    >
                                        {{ statusConfig[app.status].label }}
                                    </span>
                                </div>

                                <p v-if="app.message" class="text-sm text-gray-600 mt-1.5 leading-relaxed line-clamp-3">
                                    {{ app.message }}
                                </p>

                                <div class="flex items-center justify-between flex-wrap gap-3 mt-3">
                                    <div class="flex items-center gap-3">
                                        <span v-if="app.budget_offer" class="text-xs font-semibold text-navy bg-navy/5 px-2 py-0.5 rounded">
                                            €{{ parseFloat(app.budget_offer).toFixed(2) }}
                                        </span>
                                        <span class="text-xs text-gray-400">{{ formatDate(app.created_at) }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="route('seeker.public-profile', app.applicant.id)"
                                            class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-navy border border-gray-200 hover:border-navy/30 rounded-lg px-3 py-1.5 transition-colors"
                                        >
                                            Skatīt profilu
                                        </Link>

                                        <template v-if="app.status === 'pending'">
                                            <button
                                                @click="reject(app)"
                                                :disabled="loading === app.id"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 rounded-lg px-3 py-1.5 transition-colors disabled:opacity-50"
                                            >
                                                <XCircleIcon class="w-3.5 h-3.5" />
                                                Noraidīt
                                            </button>
                                            <button
                                                @click="accept(app)"
                                                :disabled="loading === app.id"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-white bg-gold hover:bg-yellow-400 rounded-lg px-3 py-1.5 transition-colors disabled:opacity-50"
                                            >
                                                <CheckCircleIcon class="w-3.5 h-3.5" />
                                                Pieņemt
                                            </button>
                                        </template>

                                        <Link
                                            v-if="app.status === 'accepted'"
                                            :href="route('chat.index')"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-white bg-navy hover:bg-navy/90 rounded-lg px-3 py-1.5 transition-colors"
                                        >
                                            <ChatBubbleLeftIcon class="w-3.5 h-3.5" />
                                            Sazināties
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
