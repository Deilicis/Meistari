<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import WelcomeServiceCard from '@/Components/Welcome/WelcomeServiceCard.vue';
import WelcomeJobCard from '@/Components/Welcome/WelcomeJobCard.vue';
import type { WelcomeServiceCard as ServiceCardType, WelcomeJobRequestCard } from '@/types/welcome';

defineProps<{
    services: ServiceCardType[];
    jobRequests: WelcomeJobRequestCard[];
    activeTab: 'services' | 'jobs';
    hasActiveFilters: boolean;
    canRegister?: boolean;
}>();

const emit = defineEmits<{
    switchTab: [tab: 'services' | 'jobs'];
    open: [];
    clearFilters: [];
}>();
</script>

<template>
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
                <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
                    <button
                        @click="emit('switchTab', 'services')"
                        class="px-5 py-2 text-sm font-semibold rounded-lg transition-colors"
                        :class="activeTab === 'services' ? 'bg-navy text-white shadow-sm' : 'text-gray-500 hover:text-navy'"
                    >
                        Pakalpojumi
                        <span class="ml-1.5 text-xs opacity-60">{{ services.length }}</span>
                    </button>
                    <button
                        @click="emit('switchTab', 'jobs')"
                        class="px-5 py-2 text-sm font-semibold rounded-lg transition-colors"
                        :class="activeTab === 'jobs' ? 'bg-navy text-white shadow-sm' : 'text-gray-500 hover:text-navy'"
                    >
                        Darba sludinājumi
                        <span class="ml-1.5 text-xs opacity-60">{{ jobRequests.length }}</span>
                    </button>
                </div>

                <p v-if="hasActiveFilters" class="text-sm text-gray-500">
                    Filtrēts results
                    <button @click="emit('clearFilters')" class="ml-2 text-xs font-semibold text-navy hover:underline">
                        Notīrīt
                    </button>
                </p>
            </div>

            <div v-if="activeTab === 'services'">
                <div v-if="services.length === 0" class="text-center py-16 text-gray-400">
                    <MagnifyingGlassIcon class="w-10 h-10 mx-auto mb-3 opacity-30" />
                    <p class="text-sm font-medium">Nav atrasts neviens pakalpojums.</p>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <WelcomeServiceCard
                        v-for="svc in services"
                        :key="svc.id"
                        :service="svc"
                        @open="emit('open')"
                    />
                </div>
            </div>

            <div v-if="activeTab === 'jobs'">
                <div v-if="jobRequests.length === 0" class="text-center py-16 text-gray-400">
                    <MagnifyingGlassIcon class="w-10 h-10 mx-auto mb-3 opacity-30" />
                    <p class="text-sm font-medium">Nav atrasts neviens sludinājums.</p>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <WelcomeJobCard
                        v-for="jr in jobRequests"
                        :key="jr.id"
                        :job="jr"
                        @open="emit('open')"
                    />
                </div>
            </div>

            <div class="mt-10 text-center">
                <p class="text-sm text-gray-400 mb-3">Apskata tikai 8 no visiem pieejamajiem ierakstiem</p>
                <Link v-if="canRegister" :href="route('register')"
                    class="inline-flex items-center gap-2 bg-navy text-white text-sm font-bold px-6 py-2.5 rounded-xl hover:bg-navy-hover transition-colors">
                    Reģistrēties un skatīt visus
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>
        </div>
    </section>
</template>
