<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type Tab = 'seeker' | 'master';
const active = ref<Tab>('seeker');

const seekerIcons = [
    'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
];

const masterIcons = [
    'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
    'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
];

const seekerSteps = computed(() => [
    { icon: seekerIcons[0], title: t('welcome.how_it_works.seeker_post_title'),    desc: t('welcome.how_it_works.seeker_post_desc') },
    { icon: seekerIcons[1], title: t('welcome.how_it_works.seeker_receive_title'), desc: t('welcome.how_it_works.seeker_receive_desc') },
    { icon: seekerIcons[2], title: t('welcome.how_it_works.seeker_choose_title'),  desc: t('welcome.how_it_works.seeker_choose_desc') },
]);

const masterSteps = computed(() => [
    { icon: masterIcons[0], title: t('welcome.how_it_works.master_profile_title'), desc: t('welcome.how_it_works.master_profile_desc') },
    { icon: masterIcons[1], title: t('welcome.how_it_works.master_find_title'),    desc: t('welcome.how_it_works.master_find_desc') },
    { icon: masterIcons[2], title: t('welcome.how_it_works.master_start_title'),   desc: t('welcome.how_it_works.master_start_desc') },
]);
</script>

<template>
    <section class="py-16 bg-white border-t border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-navy">{{ t('welcome.how_it_works.title') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ t('welcome.how_it_works.subtitle') }}</p>
            </div>

            <!-- Tabs -->
            <div class="flex justify-center mb-8">
                <div class="flex gap-1 bg-gray-100 rounded-xl p-1">
                    <button
                        @click="active = 'seeker'"
                        class="px-5 py-2 rounded-lg text-sm font-semibold transition-all"
                        :class="active === 'seeker'
                            ? 'bg-white text-navy shadow-sm'
                            : 'text-gray-500 hover:text-gray-700'"
                    >
                        <span
                            class="border-b-2 pb-0.5 transition-colors"
                            :class="active === 'seeker' ? 'border-emerald-400' : 'border-transparent'"
                        >
                            {{ t('welcome.how_it_works.for_seekers') }}
                        </span>
                    </button>
                    <button
                        @click="active = 'master'"
                        class="px-5 py-2 rounded-lg text-sm font-semibold transition-all"
                        :class="active === 'master'
                            ? 'bg-white text-navy shadow-sm'
                            : 'text-gray-500 hover:text-gray-700'"
                    >
                        <span
                            class="border-b-2 pb-0.5 transition-colors"
                            :class="active === 'master' ? 'border-gold' : 'border-transparent'"
                        >
                            {{ t('welcome.how_it_works.for_masters') }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Seeker steps -->
            <div v-show="active === 'seeker'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="(step, i) in seekerSteps"
                    :key="i"
                    class="bg-gray-50 rounded-2xl border border-gray-100 p-6 flex gap-4"
                >
                    <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="step.icon" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-500 uppercase tracking-wide mb-1">{{ t('welcome.how_it_works.step', { n: i + 1 }) }}</div>
                        <h3 class="text-sm font-bold text-navy mb-1">{{ step.title }}</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>

            <!-- Master steps -->
            <div v-show="active === 'master'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="(step, i) in masterSteps"
                    :key="i"
                    class="bg-gray-50 rounded-2xl border border-gray-100 p-6 flex gap-4"
                >
                    <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="step.icon" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gold uppercase tracking-wide mb-1">{{ t('welcome.how_it_works.step', { n: i + 1 }) }}</div>
                        <h3 class="text-sm font-bold text-navy mb-1">{{ step.title }}</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
