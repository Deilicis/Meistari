<script setup lang="ts">
import { computed } from 'vue';
import { MapPinIcon } from '@heroicons/vue/24/outline';
import * as HeroIcons from '@heroicons/vue/24/outline';
import type { Service } from '@/types/models';

const props = defineProps<{
    service: Service;
}>();

const emit = defineEmits(['edit', 'delete']);

const iconComponent = computed(() => {
    const icon = props.service.category?.icon;
    if (!icon) return null;
    return (HeroIcons as Record<string, unknown>)[icon] ?? null;
});

const formatPrice = (service: Service): string => {
    if (service.price_type === 'negotiable' || !service.price) return 'Vienojoties';
    const suffix = service.price_type === 'hourly' ? ' €/h' : ' €';
    return service.price + suffix;
};
</script>

<template>
    <div class="group bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow flex items-stretch">

        <!-- Left: category icon -->
        <div class="w-16 shrink-0 flex items-center justify-center px-3 border-r border-gray-100">
            <div class="w-10 h-10 rounded-xl bg-navy/5 flex items-center justify-center">
                <component :is="iconComponent" v-if="iconComponent" class="w-5 h-5 text-navy/70" />
                <svg v-else class="w-5 h-5 text-navy/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </div>
        </div>

        <!-- Middle: category, status, title, description, location -->
        <div class="flex-1 min-w-0 px-4 py-4">
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span v-if="service.category" class="text-xs font-semibold text-gray-400 tracking-wide uppercase">
                    {{ service.category.name }}
                </span>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                    :class="service.is_active
                        ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200'
                        : 'bg-red-100 text-red-800 ring-1 ring-red-200'"
                >
                    {{ service.is_active ? 'Aktīvs' : 'Neaktīvs' }}
                </span>
            </div>
            <h3 class="text-sm font-bold text-navy mb-1 line-clamp-1">{{ service.title }}</h3>
            <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ service.description }}</p>
            <div v-if="service.location && service.location.length > 0" class="flex flex-wrap gap-1">
                <span
                    v-for="loc in service.location.slice(0, 2)"
                    :key="loc"
                    class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 rounded px-1.5 py-0.5 border border-gray-100"
                >
                    <MapPinIcon class="w-2.5 h-2.5 text-gray-400" />
                    {{ loc }}
                </span>
                <span v-if="service.location.length > 2" class="text-xs text-gray-400 self-center">
                    +{{ service.location.length - 2 }}
                </span>
            </div>
        </div>

        <!-- Right: price + edit/delete -->
        <div class="shrink-0 flex flex-col items-end justify-between px-4 py-4 min-w-[90px]">
            <span class="text-sm font-bold text-navy">{{ formatPrice(service) }}</span>
            <div class="flex items-center gap-1">
                <button
                    @click="emit('edit', service)"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-navy hover:bg-navy/5 transition-colors"
                    title="Rediģēt"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button
                    @click.stop="emit('delete', service.id)"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                    title="Dzēst"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>

    </div>
</template>
