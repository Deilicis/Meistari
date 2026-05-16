<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { MapPinIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
import type { WelcomeServiceCard as ServiceCardType } from '@/types/welcome';

const { t } = useI18n();

const props = defineProps<{ service: ServiceCardType }>();
const emit = defineEmits<{ open: [] }>();

const masterName = computed(() => {
    const p = props.service.user.profile;
    if (!p) return props.service.user.name;
    if (p.type === 'company') return p.company_name ?? props.service.user.name;
    const parts = [p.first_name, p.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : props.service.user.name;
});

const initials = computed(() => masterName.value.slice(0, 2).toUpperCase());

const formattedPrice = computed(() => {
    const s = props.service;
    if (s.price_type === 'negotiable') return 'Vienojoties';
    if (!s.price) return '-';
    const f = new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(s.price);
    return s.price_type === 'hourly' ? `${f}/h` : f;
});
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow flex flex-col">
        <div class="flex items-center gap-3 px-4 pt-4 pb-3 border-b border-gray-50">
            <div class="w-9 h-9 rounded-full bg-navy flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                <img v-if="service.user.profile?.avatar" :src="`/storage/${service.user.profile.avatar}`" :alt="masterName" class="w-full h-full object-cover" />
                <span v-else>{{ initials }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1">
                    <p class="text-xs font-semibold text-gray-800 truncate">{{ masterName }}</p>
                    <CheckBadgeIcon v-if="service.user.profile?.is_verified" class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                </div>
                <p v-if="service.user.profile?.city" class="text-xs text-gray-400 truncate flex items-center gap-0.5">
                    <MapPinIcon class="w-2.5 h-2.5 shrink-0" />
                    {{ service.user.profile.city }}
                </p>
            </div>
        </div>

        <div class="px-4 py-3 flex-1">
            <span v-if="service.category" class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ service.category.name }}</span>
            <h3 class="text-sm font-bold text-navy mt-1 mb-1 line-clamp-1">{{ service.title }}</h3>
            <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ service.description }}</p>
        </div>

        <div class="px-4 pb-4 flex items-center justify-between gap-2">
            <div>
                <span class="text-base font-extrabold text-navy">{{ formattedPrice }}</span>
                <div v-if="service.location?.length" class="flex items-center gap-0.5 mt-0.5">
                    <MapPinIcon class="w-3 h-3 text-gray-300 shrink-0" />
                    <span class="text-xs text-gray-400 truncate">{{ service.location[0] }}</span>
                </div>
            </div>
            <button
                @click="emit('open')"
                class="px-4 py-1.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-navy-hover transition-colors shrink-0"
            >
                {{ t('services.card_view_btn') }}
            </button>
        </div>
    </div>
</template>
