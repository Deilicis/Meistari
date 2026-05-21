<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import { formatCurrency } from '@/utils/formatters';
import Modal from '@/Components/Common/Modal.vue';
import {
    XMarkIcon,
    CheckIcon,
    XCircleIcon,
    CheckBadgeIcon,
    CurrencyEuroIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';
import type { JobRequest, JobApplication } from '@/types/models';

const { t } = useI18n();

const props = defineProps<{
    show: boolean;
    job: JobRequest | null;
    applications: JobApplication[];
    acceptedMasterId?: number | null;
}>();

const emit = defineEmits<{
    close: [];
    updated: [];
    review: [revieweeId: number, jobRequestId: number];
}>();

const processing = ref<number | null>(null);

const statusBadgeClass: Record<string, string> = {
    pending:   'bg-amber-100 text-amber-700',
    accepted:  'bg-emerald-100 text-emerald-700',
    rejected:  'bg-red-100 text-red-700',
    completed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-gray-100 text-gray-600',
};

const acceptedApplication = computed(() =>
    props.applications.find(a => a.status === 'accepted') ?? null
);

const applicantName = (app: JobApplication): string => {
    const p = app.user.profile;
    if (!p) return app.user.name;
    if (p.type === 'company') return p.company_name ?? app.user.name;
    const parts = [p.first_name, p.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : app.user.name;
};

const accept = async (app: JobApplication) => {
    processing.value = app.id;
    try {
        await axios.patch(route('api.applications.accept', app.id));
        toast.success(t('jobs.accept_app_success'));
        emit('updated');
    } catch (e: any) {
        toast.error(e.response?.data?.message ?? t('jobs.accept_app_error'));
    } finally {
        processing.value = null;
    }
};

const reject = async (app: JobApplication) => {
    processing.value = app.id;
    try {
        await axios.patch(route('api.applications.reject', app.id));
        toast.success(t('jobs.reject_app_success'));
        emit('updated');
    } catch (e: any) {
        toast.error(e.response?.data?.message ?? t('jobs.reject_app_error'));
    } finally {
        processing.value = null;
    }
};

</script>

<template>
    <Modal :show="show" @close="emit('close')" maxWidth="2xl">
        <div v-if="job" class="flex flex-col max-h-[85vh]">

            
            <div class="bg-navy px-6 py-4 flex items-start justify-between gap-4 flex-shrink-0">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-gold uppercase tracking-widest mb-0.5">{{ t('jobs.submitted_applications') }}</p>
                    <a
                        :href="route('jobs.show', job.id)"
                        class="text-base font-bold text-white leading-snug truncate hover:text-gold transition-colors block"
                    >{{ job.title }}</a>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="text-xs font-bold text-white/60 bg-white/10 px-2.5 py-1 rounded-full">
                        {{ applications.length }}
                    </span>
                    <button @click="emit('close')" class="text-white/60 hover:text-white transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            
            <div class="overflow-y-auto flex-grow p-5 space-y-3">

                
                <div v-if="applications.length === 0" class="py-12 text-center">
                    <ClipboardDocumentListIcon class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                    <p class="text-sm font-medium text-gray-500">{{ t('jobs.no_applications_msg') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ t('jobs.applications_coming_msg') }}</p>
                </div>

                
                <div
                    v-for="app in applications"
                    :key="app.id"
                    class="rounded-xl border p-4 transition-all"
                    :class="app.status === 'accepted'
                        ? 'border-emerald-200 bg-emerald-50/50'
                        : app.status === 'rejected'
                            ? 'border-gray-100 bg-gray-50 opacity-60'
                            : 'border-gray-100 bg-white'"
                >
                    <div class="flex items-start gap-3">
                        
                        <div class="w-10 h-10 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden">
                            <img
                                v-if="app.user.profile?.avatar"
                                :src="`/storage/${app.user.profile.avatar}`"
                                :alt="applicantName(app)"
                                class="w-full h-full object-cover"
                            />
                            <span v-else>{{ applicantName(app).slice(0, 2).toUpperCase() }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <a
                                    :href="route('master.public-profile', app.user.id)"
                                    class="text-sm font-bold text-gray-900 hover:text-navy hover:underline transition-colors"
                                >{{ applicantName(app) }}</a>
                                <span
                                    v-if="app.status === 'accepted'"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700"
                                >
                                    <CheckBadgeIcon class="w-3.5 h-3.5" />
                                    {{ t('statuses.application.accepted') }}
                                </span>
                                <span
                                    v-else
                                    class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                    :class="statusBadgeClass[app.status]"
                                >
                                    {{ t('statuses.application.' + app.status) }}
                                </span>
                            </div>

                            <p v-if="app.user.profile?.city" class="text-xs text-gray-400 mb-2">{{ app.user.profile.city }}</p>

                            
                            <div v-if="app.price_offer !== null" class="flex items-center gap-1.5 text-sm font-semibold text-navy mb-2">
                                <CurrencyEuroIcon class="w-4 h-4 text-gold" />
                                {{ formatCurrency(app.price_offer) }}
                            </div>

                            
                            <p v-if="app.cover_letter" class="text-sm text-gray-600 leading-relaxed line-clamp-3">
                                {{ app.cover_letter }}
                            </p>
                            <p v-else class="text-xs text-gray-400 italic">{{ t('jobs.no_cover_letter') }}</p>
                        </div>

                        
                        <div v-if="job.status === 'open' && app.status === 'pending'" class="flex flex-col gap-2 flex-shrink-0">
                            <button
                                @click="accept(app)"
                                :disabled="processing !== null"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors disabled:opacity-50"
                            >
                                <CheckIcon class="w-3.5 h-3.5" />
                                {{ t('jobs.accept_app_btn') }}
                            </button>
                            <button
                                @click="reject(app)"
                                :disabled="processing !== null"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg transition-colors disabled:opacity-50"
                            >
                                <XCircleIcon class="w-3.5 h-3.5" />
                                {{ t('jobs.reject_proposal_btn') }}
                            </button>
                        </div>

                        
                        <div v-else-if="job.status === 'completed' && app.status === 'completed'" class="flex-shrink-0">
                            <button
                                @click="emit('review', app.user.id, job.id)"
                                class="px-3 py-1.5 text-xs font-semibold text-navy border border-navy/30 hover:bg-navy/5 rounded-lg transition-colors"
                            >
                                {{ t('jobs.leave_review_btn') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-3 flex-shrink-0 bg-white">
                <button
                    @click="emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    {{ t('common.close') }}
                </button>

                <div class="flex items-center gap-3">
                    <div
                        v-if="job.status === 'completed'"
                        class="text-sm font-semibold text-emerald-700 flex items-center gap-2"
                    >
                        <CheckBadgeIcon class="w-4 h-4" />
                        {{ t('jobs.completed_badge') }}
                    </div>
                    <a
                        :href="route('jobs.show', job.id)"
                        class="px-4 py-2 text-sm font-semibold text-white bg-navy hover:bg-navy/90 rounded-lg transition-colors"
                    >
                        {{ t('jobs.view_job') }}
                    </a>
                </div>
            </div>
        </div>
    </Modal>
</template>
