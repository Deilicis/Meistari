<script setup lang="ts">
import type { Service } from '@/types/models';

defineProps<{
    service: Service;
}>();

const emit = defineEmits(['edit', 'delete']);

const formatPrice = (service: Service): string => {
    if (service.price_type === 'negotiable' || !service.price) return 'Vienojoties';
    const suffix = service.price_type === 'hourly' ? ' €/h' : ' €';
    return service.price + suffix;
};
</script>

<template>
    <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col transition-shadow hover:shadow-md">

        <div class="relative bg-navy px-5 pt-5 pb-6">
            <span
                class="absolute top-3 right-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                :class="service.is_active
                    ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200'
                    : 'bg-red-100 text-red-800 ring-1 ring-red-200'"
            >
                {{ service.is_active ? 'Aktīvs' : 'Neaktīvs' }}
            </span>

            <!-- Kategorijas nozīmīte -->
            <span v-if="service.category" class="inline-flex items-center gap-1 text-xs font-semibold text-gold bg-navy-light rounded px-2 py-0.5 mb-2 tracking-wide uppercase">
                {{ service.category.name }}
            </span>

            <!-- Nosaukums -->
            <h3 class="text-base font-bold text-white line-clamp-2 pr-16">
                {{ service.title }}
            </h3>
        </div>

        <!-- Kartītes saturs -->
        <div class="-mt-2 mx-4 bg-white rounded-xl px-4 py-4 flex-grow flex flex-col shadow-sm">
            <p class="text-sm text-gray-600 line-clamp-3 mb-4 flex-grow">{{ service.description }}</p>

            <!-- Papildu informācija -->
            <div class="space-y-1.5 mb-4">
                <div v-if="service.location && service.location.length > 0" class="flex items-center gap-1.5 text-xs text-gray-500">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ service.location.join(', ') }}</span>
                </div>
            </div>

            <!-- Kartītes apakšdaļa -->
            <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between">
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
                        @click="emit('delete', service.id)"
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

    </div>
</template>