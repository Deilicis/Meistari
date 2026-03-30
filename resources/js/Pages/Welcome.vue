<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import WelcomeNav from '@/Components/Welcome/WelcomeNav.vue';
import WelcomeHero from '@/Components/Welcome/WelcomeHero.vue';
import WelcomeBrowseSection from '@/Components/Welcome/WelcomeBrowseSection.vue';
import WelcomeHowItWorks from '@/Components/Welcome/WelcomeHowItWorks.vue';
import WelcomeJoinSection from '@/Components/Welcome/WelcomeJoinSection.vue';
import WelcomeFooter from '@/Components/Welcome/WelcomeFooter.vue';
import GuestGateModal from '@/Components/Welcome/GuestGateModal.vue';
import { useWelcomeFilters } from '@/composables/useWelcomeFilters';
import type { WelcomeServiceCard, WelcomeJobRequestCard, PopularCategory, WelcomeFilters } from '@/types/welcome';

const props = defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    services: WelcomeServiceCard[];
    jobRequests: WelcomeJobRequestCard[];
    popularCategories: PopularCategory[];
    filters: WelcomeFilters;
}>();

const page = usePage();
const isAuth = computed(() => !!(page.props as any).auth?.user);
const filtersRef = computed(() => props.filters);

const {
    searchInput,
    activeTab,
    activeTagId,
    submitSearch,
    selectTag,
    switchTab,
    clearFilters,
} = useWelcomeFilters(filtersRef);

const hasActiveFilters = computed(() => !!(props.filters.search || props.filters.category_id));

const showGate = ref(false);

const handleOpen = () => {
    if (!isAuth.value) {
        showGate.value = true;
        return;
    }
    if (activeTab.value === 'services') {
        router.visit(route('seeker.services.index'));
    } else {
        router.visit(route('master.job-requests.index'));
    }
};
</script>

<template>
    <Head title="Meistari - Atrodi uzticamu meistaru" />

    <div class="min-h-screen flex flex-col font-sans bg-gray-50">
        <WelcomeNav :canLogin="canLogin" :canRegister="canRegister" :isAuth="isAuth" />

        <WelcomeHero
            :popularCategories="popularCategories"
            :activeTagId="activeTagId"
            v-model="searchInput"
            @search="submitSearch"
            @selectTag="selectTag"
        />

        <WelcomeBrowseSection
            :services="services"
            :jobRequests="jobRequests"
            :activeTab="activeTab"
            :hasActiveFilters="hasActiveFilters"
            :canRegister="canRegister"
            @switchTab="switchTab"
            @open="handleOpen"
            @clearFilters="clearFilters"
        />

        <WelcomeHowItWorks />

        <WelcomeJoinSection :canRegister="canRegister" />

        <WelcomeFooter />

        <GuestGateModal
            :show="showGate"
            :canRegister="canRegister"
            @close="showGate = false"
        />
    </div>
</template>
