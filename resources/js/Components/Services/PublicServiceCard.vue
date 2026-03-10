<script setup lang="ts">
import { computed } from 'vue';
import { MapPinIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
import type { ServiceWithMaster } from '@/types/models';

const props = defineProps<{
    service: ServiceWithMaster;
    applied: boolean;
}>();

const emit = defineEmits<{
    open: [service: ServiceWithMaster];
}>();

const profile = computed(() => props.service.user?.profile ?? null);

const masterName = computed(() => {
    if (!profile.value) return props.service.user?.name ?? '—';
    if (profile.value.type === 'company') return profile.value.company_name ?? props.service.user.name;
    const parts = [profile.value.first_name, profile.value.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : props.service.user.name;
});

const avatarInitials = computed(() => {
    return masterName.value.slice(0, 2).toUpperCase();
});

const formatPrice = (): string => {
    if (props.service.price_type === 'negotiable') return 'Vienojoties';
    if (!props.service.price) return '—';
    const formatted = new Intl.NumberFormat('lv-LV', {
        style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
    }).format(props.service.price);
    return props.service.price_type === 'hourly' ? `${formatted}/h` : formatted;
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition-shadow">

        <div class="bg-navy px-5 pt-4 pb-7">
            <span v-if="service.category" class="inline-flex items-center text-xs font-semibold text-gold bg-navy-light rounded px-2 py-0.5 mb-2 tracking-wide uppercase">
                {{ service.category.name }}
            </span>
            <h3 class="text-base font-bold text-white line-clamp-2">{{ service.title }}</h3>
        </div>

        <div class="-mt-3 mx-4 bg-white rounded-xl shadow-sm border border-gray-100 px-3 py-2 flex items-center gap-2.5 flex-shrink-0">
            <div class="w-8 h-8 rounded-full bg-navy flex items-center justify-center text-white text-xs font-bold flex-shrink-0 overflow-hidden">
                <img
                    v-if="profile?.avatar"
                    :src="`/storage/${profile.avatar}`"
                    :alt="masterName"
                    class="w-full h-full object-cover"
                />
                <span v-else>{{ avatarInitials }}</span>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1">
                    <p class="text-xs font-semibold text-gray-900 truncate">{{ masterName }}</p>
                    <CheckBadgeIcon v-if="profile?.is_verified" class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" />
                </div>
                <p v-if="profile?.city" class="text-xs text-gray-400 truncate">{{ profile.city }}</p>
            </div>
        </div>

        <div class="px-5 pt-3 pb-5 flex flex-col flex-grow">
            <p class="text-sm text-gray-600 line-clamp-3 mb-4 flex-grow">{{ service.description }}</p>

            <div class="space-y-2 mb-4">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-extrabold text-navy">{{ formatPrice() }}</span>
                    <span v-if="service.price_type === 'hourly'" class="text-xs text-gray-400">stundā</span>
                </div>

                <div v-if="service.location?.length" class="flex flex-wrap gap-1.5">
                    <span
                        v-for="loc in service.location.slice(0, 2)"
                        :key="loc"
                        class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 rounded-md px-2 py-0.5 border border-gray-100"
                    >
                        <MapPinIcon class="w-3 h-3 text-gray-400" />
                        {{ loc }}
                    </span>
                    <span v-if="service.location.length > 2" class="text-xs text-gray-400 self-center">
                        +{{ service.location.length - 2 }}
                    </span>
                </div>
            </div>

            <div>
                <div
                    v-if="applied"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold"
                >
                    <CheckBadgeIcon class="w-4 h-4" />
                    Pieteikums iesniegts
                </div>
                <button
                    v-else
                    @click="emit('open', service)"
                    class="w-full px-4 py-2.5 rounded-lg bg-navy text-white text-sm font-semibold hover:bg-navy-hover transition-colors"
                >
                    Apskatīt
                </button>
            </div>
        </div>
    </div>
</template>
