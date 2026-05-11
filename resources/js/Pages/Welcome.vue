<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
import WelcomeNav from '@/Components/Welcome/WelcomeNav.vue';
import WelcomeHero from '@/Components/Welcome/WelcomeHero.vue';
import StatsBar from '@/Components/Welcome/StatsBar.vue';
import PopularCategories from '@/Components/Welcome/PopularCategories.vue';
import FeaturedMasters from '@/Components/Welcome/FeaturedMasters.vue';
import WelcomeHowItWorks from '@/Components/Welcome/WelcomeHowItWorks.vue';
import WelcomeJoinSection from '@/Components/Welcome/WelcomeJoinSection.vue';
import WelcomeFooter from '@/Components/Welcome/WelcomeFooter.vue';
import type { WelcomeStats, PopularCategory, FeaturedMaster } from '@/types/welcome';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    stats: WelcomeStats;
    popularCategories: PopularCategory[];
    featuredMasters: FeaturedMaster[];
}>();

const page = usePage();
const isAuth = computed(() => !!(page.props as any).auth?.user);
</script>

<template>
    <Head :title="t('welcome.meta_title')" />

    <div class="min-h-screen flex flex-col font-sans bg-gray-50">
        <WelcomeNav :canLogin="canLogin" :canRegister="canRegister" :isAuth="isAuth" />

        <WelcomeHero :canRegister="canRegister" />

        <StatsBar :stats="stats" />

        <PopularCategories :categories="popularCategories" :isAuth="isAuth" :canRegister="canRegister" />

        <FeaturedMasters :masters="featuredMasters" :isAuth="isAuth" :canRegister="canRegister" />

        <WelcomeHowItWorks />

        <WelcomeJoinSection :canRegister="canRegister" />

        <WelcomeFooter />
    </div>
</template>
