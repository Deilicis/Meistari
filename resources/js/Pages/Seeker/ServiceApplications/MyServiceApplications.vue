<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ServiceDetailModal from '@/Components/Services/ServiceDetailModal.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import type { ServiceApplicationWithService, ServiceApplicationStatus, ServiceWithMaster } from '@/types/models';
import {
    InboxArrowDownIcon,
    ArrowTopRightOnSquareIcon,
    XCircleIcon,
    BanknotesIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    applications: ServiceApplicationWithService[];
}>();

const activeFilter = ref<ServiceApplicationStatus | 'all'>('all');

const statusConfig: Record<ServiceApplicationStatus, { label: string; badgeClass: string; borderClass: string }> = {
    pending:   { label: 'Gaida apstiprinājumu', badgeClass: 'bg-amber-100 text-amber-700',    borderClass: 'border-l-amber-400' },
    accepted:  { label: 'Pieņemts',             badgeClass: 'bg-emerald-100 text-emerald-700', borderClass: 'border-l-emerald-500' },
    rejected:  { label: 'Noraidīts',            badgeClass: 'bg-red-100 text-red-700',         borderClass: 'border-l-red-400' },
    completed: { label: 'Pabeigts',             badgeClass: 'bg-blue-100 text-blue-700',       borderClass: 'border-l-blue-400' },
    cancelled: { label: 'Atcelts',              badgeClass: 'bg-gray-100 text-gray-500',       borderClass: 'border-l-gray-300' },
};

const priceTypeLabel: Record<string, string> = {
    hourly:     '/ stundā',
    fixed:      'fiksēta',
    negotiable: 'pēc vienošanās',
};

const tabs: { key: ServiceApplicationStatus | 'all'; label: string }[] = [
    { key: 'all',       label: 'Visi' },
    { key: 'pending',   label: 'Gaida' },
    { key: 'accepted',  label: 'Pieņemti' },
    { key: 'rejected',  label: 'Noraidīti' },
    { key: 'completed', label: 'Pabeigti' },
    { key: 'cancelled', label: 'Atcelti' },
];

const filteredApplications = computed(() => {
    if (activeFilter.value === 'all') return props.applications;
    return props.applications.filter(a => a.status === activeFilter.value);
});

const tabCount = (key: ServiceApplicationStatus | 'all') => {
    if (key === 'all') return props.applications.length;
    return props.applications.filter(a => a.status === key).length;
};

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });

// Service detail modal
const detailService = ref<ServiceWithMaster | null>(null);
const showDetailModal = ref(false);

const openDetail = (app: ServiceApplicationWithService) => {
    detailService.value = {
        ...app.service,
        user_id: app.service.user.id,
        category_id: app.service.category?.id ?? 0,
    } as ServiceWithMaster;
    showDetailModal.value = true;
};

// Cancel
const cancelTarget = ref<ServiceApplicationWithService | null>(null);
const cancelling = ref(false);

const confirmCancel = async () => {
    if (!cancelTarget.value) return;
    cancelling.value = true;
    try {
        await axios.delete(route('api.service-applications.destroy', cancelTarget.value.id));
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

        <!-- Page header -->
        <div class="bg-navy">
            <div class="h-1 bg-emerald-400" />
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <InboxArrowDownIcon class="w-6 h-6 text-emerald-400" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">Mani pieteikumi</h1>
                        <p class="text-white/50 text-sm mt-0.5">
                            Pieteikumi uz pakalpojumiem ·
                            <span class="text-emerald-400 font-semibold">{{ applications.length }}</span> kopā
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <!-- Filter tabs -->
            <div class="flex gap-1 flex-wrap border-b border-gray-200">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeFilter = tab.key"
                    class="px-3 py-2 text-sm font-medium rounded-t transition-colors relative -mb-px"
                    :class="activeFilter === tab.key
                        ? 'text-navy border-b-2 border-navy bg-navy/5'
                        : 'text-gray-500 hover:text-navy hover:bg-gray-50'"
                >
                    {{ tab.label }}
                    <span
                        v-if="tabCount(tab.key) > 0"
                        class="ml-1.5 text-xs font-bold px-1.5 py-0.5 rounded-full"
                        :class="activeFilter === tab.key ? 'bg-navy text-white' : 'bg-gray-200 text-gray-600'"
                    >
                        {{ tabCount(tab.key) }}
                    </span>
                </button>
            </div>

            <!-- Application cards -->
            <div v-if="filteredApplications.length > 0" class="space-y-3">
                <div
                    v-for="app in filteredApplications"
                    :key="app.id"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                >
                    <!-- Top bar: title, category, status, date -->
                    <div
                        class="px-5 pt-5 pb-4 border-l-4"
                        :class="statusConfig[app.status].borderClass"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                    <h3 class="font-bold text-navy text-base leading-snug">
                                        {{ app.service.title }}
                                    </h3>
                                    <span
                                        v-if="app.service.category"
                                        class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
                                    >
                                        {{ app.service.category.name }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                    <UserIcon class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                    <a
                                        :href="route('master.public-profile', app.service.user.id)"
                                        class="font-medium text-gray-700 hover:text-navy hover:underline transition-colors"
                                    >{{ app.service.user.name }}</a>
                                    <span v-if="app.service.user.profile?.city" class="text-gray-400">
                                        · {{ app.service.user.profile.city }}
                                    </span>
                                </div>
                            </div>
                            <div class="shrink-0 flex flex-col items-end gap-1.5">
                                <span
                                    class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                    :class="statusConfig[app.status].badgeClass"
                                >
                                    {{ statusConfig[app.status].label }}
                                </span>
                                <span class="text-xs text-gray-400">{{ formatDate(app.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Body: message + price meta -->
                    <div class="px-5 pb-4 space-y-3">
                        <p v-if="app.message" class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                            {{ app.message }}
                        </p>
                        <p v-else class="text-sm text-gray-400 italic">Nav pievienota ziņa.</p>

                        <div class="flex items-center gap-5 flex-wrap text-sm">
                            <span class="flex items-center gap-1.5 text-gray-500">
                                <BanknotesIcon class="w-3.5 h-3.5 text-gray-400" />
                                <span v-if="app.service.price != null">
                                    <span class="font-semibold text-gray-800">€{{ app.service.price }}</span>
                                    {{ priceTypeLabel[app.service.price_type] }}
                                </span>
                                <span v-else class="italic text-gray-400">{{ priceTypeLabel[app.service.price_type] }}</span>
                            </span>
                            <span
                                v-if="app.budget_offer != null"
                                class="flex items-center gap-1.5 text-gray-500"
                            >
                                <BanknotesIcon class="w-3.5 h-3.5 text-emerald-500" />
                                Mans piedāvājums:
                                <span class="font-semibold text-emerald-700">€{{ app.budget_offer }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Action bar -->
                    <div class="bg-gray-50 border-t border-gray-100 px-5 py-3 flex items-center justify-between gap-3">
                        <button
                            @click="openDetail(app)"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-navy hover:underline transition-colors"
                        >
                            <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                            Skatīt pakalpojumu
                        </button>

                        <button
                            v-if="app.status === 'pending'"
                            @click="cancelTarget = app"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 hover:border-red-300 rounded-lg px-3 py-1.5 transition-colors"
                        >
                            <XCircleIcon class="w-3.5 h-3.5" />
                            Atcelt pieteikumu
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-16 text-center">
                <div class="mx-auto w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-4">
                    <InboxArrowDownIcon class="w-6 h-6 text-emerald-300" />
                </div>
                <p class="text-base font-semibold text-gray-700 mb-1">
                    {{ activeFilter === 'all' ? 'Vēl nav neviena pieteikuma' : 'Nav pieteikumu šajā kategorijā' }}
                </p>
                <p class="text-sm text-gray-400">
                    {{ activeFilter === 'all' ? 'Atrodiet pakalpojumus un pieteicieties!' : 'Izmēģiniet citu filtru.' }}
                </p>
            </div>

        </div>
    </AuthenticatedLayout>

    <ServiceDetailModal
        :show="showDetailModal"
        :service="detailService"
        :applied="true"
        @close="showDetailModal = false"
        @apply="() => {}"
    />

    <ConfirmDialog
        :show="cancelTarget !== null"
        title="Atcelt pieteikumu?"
        :message="cancelTarget ? `Vai tiešām vēlaties atcelt pieteikumu pakalpojumam &quot;${cancelTarget.service.title}&quot;?` : ''"
        confirmLabel="Atcelt pieteikumu"
        :processing="cancelling"
        @confirm="confirmCancel"
        @cancel="cancelTarget = null"
    />
</template>
