<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { MapPinIcon, CalendarIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
import type { WelcomeJobRequestCard } from '@/types/welcome';

const { t } = useI18n();

const props = defineProps<{ job: WelcomeJobRequestCard }>();
const emit = defineEmits<{ open: [] }>();

const seekerName = computed(() => {
    const p = props.job.user.profile;
    if (!p) return props.job.user.name;
    if (p.type === 'company') return p.company_name ?? props.job.user.name;
    const parts = [p.first_name, p.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : props.job.user.name;
});

const initials = computed(() => seekerName.value.slice(0, 2).toUpperCase());

const formattedBudget = computed(() => {
    if (!props.job.budget) return 'Vienojams';
    return new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(props.job.budget);
});

const formattedDate = computed(() => {
    if (!props.job.deadline) return '';
    return new Date(props.job.deadline).toLocaleString('lv-LV', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
});
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow flex flex-col">
        <div class="flex items-center gap-3 px-4 pt-4 pb-3 border-b border-gray-50">
            <div class="w-9 h-9 rounded-full bg-navy flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                <img v-if="job.user.profile?.avatar" :src="`/storage/${job.user.profile.avatar}`" :alt="seekerName" class="w-full h-full object-cover" />
                <span v-else>{{ initials }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-gray-800 truncate">{{ seekerName }}</p>
                <p v-if="job.user.profile?.city" class="text-xs text-gray-400 truncate flex items-center gap-0.5">
                    <MapPinIcon class="w-2.5 h-2.5 shrink-0" />
                    {{ job.user.profile.city }}
                </p>
            </div>
        </div>

        <div class="px-4 py-3 flex-1">
            <span v-if="job.category" class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ job.category.name }}</span>
            <h3 class="text-sm font-bold text-navy mt-1 mb-1 line-clamp-1">{{ job.title }}</h3>
            <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ job.description }}</p>
        </div>

        <div class="px-4 pb-4 flex items-center justify-between gap-2">
            <div>
                <span class="text-base font-extrabold text-navy">{{ formattedBudget }}</span>
                <div class="flex items-center gap-2 mt-0.5">
                    <span v-if="job.deadline" class="flex items-center gap-0.5 text-xs text-gray-400">
                        <CalendarIcon class="w-3 h-3 shrink-0" />
                        {{ formattedDate }}
                    </span>
                    <span class="flex items-center gap-0.5 text-xs text-gray-400">
                        <UserGroupIcon class="w-3 h-3 shrink-0" />
                        {{ job.applications_count }}
                    </span>
                </div>
            </div>
            <button
                @click="emit('open')"
                class="px-4 py-1.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy-hover transition-colors shrink-0"
            >
                {{ t('job_requests.view_btn') }}
            </button>
        </div>
    </div>
</template>
