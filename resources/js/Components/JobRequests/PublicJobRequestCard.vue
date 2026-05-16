<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { MapPinIcon, CheckBadgeIcon, CalendarDaysIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
import * as HeroIcons from '@heroicons/vue/24/outline';

const { t } = useI18n();
import type { JobRequestWithSeeker } from '@/types/models';

const props = defineProps<{
    job: JobRequestWithSeeker;
    applied: boolean;
}>();

const emit = defineEmits<{
    open: [job: JobRequestWithSeeker];
}>();

const profile = computed(() => props.job.user?.profile ?? null);

const seekerName = computed(() => {
    if (!profile.value) return props.job.user?.name ?? '-';
    if (profile.value.type === 'company') return profile.value.company_name ?? props.job.user.name;
    const parts = [profile.value.first_name, profile.value.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : props.job.user.name;
});

const avatarInitials = computed(() => seekerName.value.slice(0, 2).toUpperCase());

const iconComponent = computed(() => {
    const icon = props.job.category?.icon;
    if (!icon) return null;
    return (HeroIcons as Record<string, unknown>)[icon] ?? null;
});

const formatDeadline = (d: string) =>
    new Date(d).toLocaleString('lv-LV', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

const formatBudget = (): string => {
    if (!props.job.budget) return 'Vienojams';
    return new Intl.NumberFormat('lv-LV', {
        style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
    }).format(props.job.budget);
};
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow flex items-stretch">

        <!-- Left: avatar + seeker info -->
        <a
            :href="route('seeker.public-profile', job.user.id)"
            class="w-28 shrink-0 flex flex-col items-center justify-center gap-1.5 px-3 py-4 border-r border-gray-100 hover:bg-gray-50 transition-colors"
            @click.stop
        >
            <div class="w-12 h-12 rounded-full bg-navy flex items-center justify-center text-white text-sm font-bold overflow-hidden shrink-0">
                <img
                    v-if="profile?.avatar"
                    :src="`/storage/${profile.avatar}`"
                    :alt="seekerName"
                    class="w-full h-full object-cover"
                />
                <span v-else>{{ avatarInitials }}</span>
            </div>
            <div class="text-center min-w-0 w-full">
                <p class="text-xs font-semibold text-gray-900 truncate">{{ seekerName }}</p>
                <div v-if="profile?.city" class="flex items-center justify-center gap-0.5 mt-0.5">
                    <MapPinIcon class="w-3 h-3 text-gray-400 shrink-0" />
                    <p class="text-xs text-gray-400 truncate">{{ profile.city }}</p>
                </div>
            </div>
        </a>

        <!-- Middle: icon + category, title, description, location/deadline -->
        <div class="flex-1 min-w-0 px-4 py-4">
            <div class="flex items-center gap-2.5 mb-2">
                <div class="w-9 h-9 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                    <component :is="iconComponent" v-if="iconComponent" class="w-5 h-5 text-navy/70" />
                    <svg v-else class="w-5 h-5 text-navy/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>
                <span v-if="job.category" class="text-xs font-semibold text-gray-400 tracking-wide uppercase">
                    {{ job.category.name }}
                </span>
            </div>
            <h3 class="text-sm font-bold text-navy mb-1 line-clamp-1">{{ job.title }}</h3>
            <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ job.description }}</p>
            <div class="flex flex-wrap gap-1">
                <span
                    v-if="job.location?.length"
                    class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 rounded px-1.5 py-0.5 border border-gray-100"
                >
                    <MapPinIcon class="w-2.5 h-2.5 text-gray-400" />
                    {{ job.location[0] }}
                </span>
                <span
                    v-if="job.deadline"
                    class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 rounded px-1.5 py-0.5 border border-gray-100"
                >
                    <CalendarDaysIcon class="w-2.5 h-2.5 text-gray-400" />
                    {{ formatDeadline(job.deadline) }}
                </span>
            </div>
        </div>

        <!-- Right: budget + applications count + action -->
        <div class="shrink-0 flex flex-col items-end justify-between px-4 py-4 min-w-[120px]">
            <div class="text-right">
                <span class="text-base font-extrabold text-navy block">{{ formatBudget() }}</span>
                <span class="inline-flex items-center gap-1 text-xs text-gray-400 mt-0.5">
                    <UserGroupIcon class="w-3.5 h-3.5" />
                    {{ job.applications_count }}
                </span>
            </div>
            <div
                v-if="applied"
                class="flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2 text-sm font-semibold"
            >
                <CheckBadgeIcon class="w-4 h-4" />
                Pieteicies
            </div>
            <button
                v-else
                @click="emit('open', job)"
                class="px-5 py-2 rounded-lg bg-navy text-white text-sm font-semibold hover:bg-navy-hover transition-colors whitespace-nowrap"
            >
                {{ t('job_requests.view_btn') }}
            </button>
        </div>

    </div>
</template>
