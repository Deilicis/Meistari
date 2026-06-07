<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ArrowLeftIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    CheckBadgeIcon,
    XCircleIcon,
    StarIcon,
} from '@heroicons/vue/24/outline';

interface JobRequest {
    id: number;
    title: string;
    status: string;
    created_at: string;
    category: { id: number; name: string } | null;
}

interface Review {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    reviewer: {
        id: number;
        name: string;
        profile: { avatar: string | null } | null;
    };
}

interface Seeker {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    profile: {
        city: string | null;
        phone: string | null;
        bio: string | null;
        is_verified: boolean;
        avatar: string | null;
    } | null;
    job_requests: JobRequest[];
    reviews_received: Review[];
}

defineProps<{ seeker: Seeker }>();

const { t } = useI18n();

const jobStatusLabel = (status: string) => {
    const map: Record<string, string> = {
        open:                  t('admin.dashboard.status_open'),
        accepted:              t('admin.dashboard.status_accepted'),
        in_progress:           t('admin.dashboard.status_in_progress'),
        awaiting_confirmation: t('admin.dashboard.status_awaiting'),
        completed:             t('admin.dashboard.status_completed'),
        disputed:              t('admin.dashboard.status_disputed'),
        cancelled:             t('admin.dashboard.status_cancelled'),
    };
    return map[status] ?? status;
};

const jobStatusClass = (status: string) => {
    const map: Record<string, string> = {
        open:                  'bg-emerald-100 text-emerald-700',
        accepted:              'bg-blue-100 text-blue-700',
        in_progress:           'bg-amber-100 text-amber-700',
        awaiting_confirmation: 'bg-orange-100 text-orange-700',
        completed:             'bg-indigo-100 text-indigo-700',
        disputed:              'bg-red-100 text-red-700',
        cancelled:             'bg-gray-100 text-gray-500',
    };
    return map[status] ?? 'bg-gray-100 text-gray-500';
};

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });
</script>

<template>
    <Head :title="seeker.name" />
    <AdminLayout>
        
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <Link
                    :href="route('admin.seekers.index')"
                    class="inline-flex items-center gap-1.5 text-white/50 hover:text-white text-sm mb-4 transition-colors"
                >
                    <ArrowLeftIcon class="w-4 h-4" />
                    Atpakaļ uz meklētājiem
                </Link>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center text-xl font-extrabold text-white shrink-0">
                        <img
                            v-if="seeker.profile?.avatar"
                            :src="`/storage/${seeker.profile.avatar}`"
                            class="w-14 h-14 rounded-full object-cover"
                            :alt="seeker.name"
                        />
                        <span v-else>{{ seeker.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ seeker.name }}</h1>
                        <p class="text-white/50 text-sm mt-0.5">Reģistrēts: {{ formatDate(seeker.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-navy px-6 py-4">
                        <h2 class="text-sm font-bold text-white">Profila informācija</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <EnvelopeIcon class="w-4 h-4 text-gray-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-400">E-pasts</p>
                                <p class="text-sm font-medium text-gray-900">{{ seeker.email }}</p>
                            </div>
                        </div>

                        <div v-if="seeker.profile?.phone" class="flex items-center gap-3">
                            <PhoneIcon class="w-4 h-4 text-gray-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-400">Tālrunis</p>
                                <p class="text-sm font-medium text-gray-900">{{ seeker.profile.phone }}</p>
                            </div>
                        </div>

                        <div v-if="seeker.profile?.city" class="flex items-center gap-3">
                            <MapPinIcon class="w-4 h-4 text-gray-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-400">Pilsēta</p>
                                <p class="text-sm font-medium text-gray-900">{{ seeker.profile.city }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <component
                                :is="seeker.profile?.is_verified ? CheckBadgeIcon : XCircleIcon"
                                class="w-4 h-4 shrink-0"
                                :class="seeker.profile?.is_verified ? 'text-emerald-500' : 'text-gray-400'"
                            />
                            <div>
                                <p class="text-xs text-gray-400">Verifikācija</p>
                                <p class="text-sm font-medium"
                                    :class="seeker.profile?.is_verified ? 'text-emerald-600' : 'text-gray-500'">
                                    {{ seeker.profile?.is_verified ? 'Verificēts' : 'Neverificēts' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="seeker.profile?.bio" class="pt-2 border-t border-gray-50">
                            <p class="text-xs text-gray-400 mb-1">Par sevi</p>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ seeker.profile.bio }}</p>
                        </div>
                    </div>
                </div>

                
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-navy px-6 py-4">
                        <h2 class="text-sm font-bold text-white">
                            Darba sludinājumi ({{ seeker.job_requests.length }})
                        </h2>
                    </div>

                    <div v-if="seeker.job_requests.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                        Nav darba sludinājumu.
                    </div>

                    <ul v-else class="divide-y divide-gray-50">
                        <li
                            v-for="jr in seeker.job_requests"
                            :key="jr.id"
                            class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50/60 transition-colors"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ jr.title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ jr.category?.name ?? '-' }} · {{ formatDate(jr.created_at) }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shrink-0"
                                :class="jobStatusClass(jr.status)"
                            >
                                {{ jobStatusLabel(jr.status) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-navy px-6 py-4">
                    <h2 class="text-sm font-bold text-white">
                        Saņemtās atsauksmes ({{ seeker.reviews_received.length }})
                    </h2>
                </div>

                <div v-if="seeker.reviews_received.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                    Nav atsauksmju.
                </div>

                <ul v-else class="divide-y divide-gray-50">
                    <li
                        v-for="review in seeker.reviews_received"
                        :key="review.id"
                        class="px-6 py-4 flex items-start gap-4 hover:bg-gray-50/60 transition-colors"
                    >
                        <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-600 shrink-0">
                            {{ review.reviewer.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ review.reviewer.name }}</p>
                                <div class="flex items-center gap-0.5 shrink-0">
                                    <StarIcon
                                        v-for="n in 5"
                                        :key="n"
                                        class="w-3.5 h-3.5"
                                        :class="n <= review.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200'"
                                    />
                                </div>
                            </div>
                            <p v-if="review.comment" class="text-sm text-gray-600 mt-1">{{ review.comment }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDate(review.created_at) }}</p>
                        </div>
                    </li>
                </ul>
            </div>

        </div>
    </AdminLayout>
</template>
