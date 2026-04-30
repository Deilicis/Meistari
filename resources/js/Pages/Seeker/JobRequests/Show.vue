<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import JobStatusBadge from '@/Components/Jobs/JobStatusBadge.vue';
import JobLifecycleTimeline from '@/Components/Jobs/JobLifecycleTimeline.vue';
import JobActionButtons from '@/Components/Jobs/JobActionButtons.vue';
import type { JobLifecycle } from '@/types/jobLifecycle';
import {
    ArrowLeftIcon,
    UserIcon,
    CurrencyEuroIcon,
    ClockIcon,
    ShieldExclamationIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{ job: JobLifecycle }>();

const job = ref<JobLifecycle>(props.job);

function formatDate(iso: string | null): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('lv-LV', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function formatPrice(amount: string | null, type: string | null): string {
    if (!amount) return '—';
    const num = parseFloat(amount).toFixed(2);
    if (type === 'hourly') return `€${num}/h`;
    return `€${num}`;
}
</script>

<template>
    <Head :title="job.title" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

            <!-- Back + header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <Link
                        :href="route('seeker.job-requests.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-navy transition-colors mb-2"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        Mani darbi
                    </Link>
                    <h1 class="text-xl font-bold text-navy leading-tight">{{ job.title }}</h1>
                </div>
                <JobStatusBadge :status="job.status" :label="job.status_label" />
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <JobLifecycleTimeline :job="job" :is-client="true" />
            </div>

            <!-- Actions -->
            <div v-if="job.allowed_actions.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-navy mb-3">Pieejamās darbības</h2>
                <JobActionButtons :job="job" @updated="job = $event" />
            </div>

            <!-- Escrow info -->
            <div v-if="job.escrow" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-navy mb-4">Maksājuma informācija</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Summa</p>
                        <p class="text-sm font-semibold text-navy">€{{ parseFloat(job.escrow.amount).toFixed(2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Statuss</p>
                        <p class="text-sm font-semibold capitalize" :class="{
                            'text-amber-600': job.escrow.status === 'held',
                            'text-emerald-600': job.escrow.status === 'released',
                            'text-gray-500': job.escrow.status === 'refunded',
                        }">
                            {{ job.escrow.status === 'held' ? 'Rezervēts' : job.escrow.status === 'released' ? 'Izmaksāts' : 'Atgriezts' }}
                        </p>
                    </div>
                    <div v-if="job.escrow.auto_release_at">
                        <p class="text-xs text-gray-500 mb-0.5">Auto-atbrīvošana</p>
                        <p class="text-sm text-gray-700">{{ formatDate(job.escrow.auto_release_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Details card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h2 class="text-sm font-bold text-navy">Darba informācija</h2>

                <p class="text-sm text-gray-700 leading-relaxed">{{ job.description }}</p>

                <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                    <!-- Master -->
                    <div class="flex items-start gap-2">
                        <UserIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">Meistars</p>
                            <p class="text-sm font-medium text-gray-800">{{ job.master?.name ?? '—' }}</p>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="flex items-start gap-2">
                        <CurrencyEuroIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">Cena</p>
                            <p class="text-sm font-medium text-gray-800">{{ formatPrice(job.agreed_price, job.price_type) }}</p>
                        </div>
                    </div>

                    <!-- Created -->
                    <div class="flex items-start gap-2">
                        <ClockIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">Izveidots</p>
                            <p class="text-sm text-gray-700">{{ formatDate(job.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Completed -->
                    <div v-if="job.completed_at" class="flex items-start gap-2">
                        <CheckCircleIcon class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-xs text-gray-500">Pabeigts</p>
                            <p class="text-sm text-gray-700">{{ formatDate(job.completed_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disputed notice -->
            <div
                v-if="job.status === 'disputed'"
                class="flex gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl"
            >
                <ShieldExclamationIcon class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-bold text-red-700">Strīds izskatīšanā</p>
                    <p class="text-xs text-red-600 mt-0.5">
                        Mūsu komanda izskata situāciju. Mēs sazināsimies ar abām pusēm tuvākajā laikā.
                    </p>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
