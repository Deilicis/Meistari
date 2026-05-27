<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const seekerSteps = computed(() => [
    { n: '01', title: t('welcome.how_it_works.seeker_post_title'),    desc: t('welcome.how_it_works.seeker_post_desc') },
    { n: '02', title: t('welcome.how_it_works.seeker_receive_title'), desc: t('welcome.how_it_works.seeker_receive_desc') },
    { n: '03', title: t('welcome.how_it_works.seeker_choose_title'),  desc: t('welcome.how_it_works.seeker_choose_desc') },
]);

const masterSteps = computed(() => [
    { n: '01', title: t('welcome.how_it_works.master_profile_title'), desc: t('welcome.how_it_works.master_profile_desc') },
    { n: '02', title: t('welcome.how_it_works.master_find_title'),    desc: t('welcome.how_it_works.master_find_desc') },
    { n: '03', title: t('welcome.how_it_works.master_start_title'),   desc: t('welcome.how_it_works.master_start_desc') },
]);
</script>

<template>
    <section class="py-20 md:py-24 bg-white border-t border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-14">
                <h2 class="text-4xl md:text-5xl font-extrabold text-navy leading-tight">
                    {{ t('welcome.how_it_works.title') }}
                </h2>
                <p class="mt-3 text-base text-gray-500 max-w-xl">
                    {{ t('welcome.how_it_works.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2">

                <div class="md:pr-10 lg:pr-16">
                    <p class="text-xs font-bold uppercase tracking-widest text-emerald-600 mb-8">
                        {{ t('welcome.how_it_works.for_seekers') }}
                    </p>
                    <div
                        v-for="step in seekerSteps"
                        :key="step.n"
                        class="flex items-start gap-5 py-7 border-b border-gray-100 last:border-b-0"
                    >
                        <div
                            class="text-7xl md:text-8xl font-black text-emerald-200 leading-none shrink-0 select-none"
                            aria-hidden="true"
                        >
                            {{ step.n }}
                        </div>
                        <div class="pt-2 min-w-0">
                            <h3 class="text-lg font-bold text-navy mb-2">{{ step.title }}</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-14 md:mt-0 md:pl-10 lg:pl-16 md:border-l border-gray-200">
                    <p class="text-xs font-bold uppercase tracking-widest text-gold mb-8">
                        {{ t('welcome.how_it_works.for_masters') }}
                    </p>
                    <div
                        v-for="step in masterSteps"
                        :key="step.n"
                        class="flex items-start gap-5 py-7 border-b border-gray-100 last:border-b-0"
                    >
                        <div
                            class="text-7xl md:text-8xl font-black text-yellow-200 leading-none shrink-0 select-none"
                            aria-hidden="true"
                        >
                            {{ step.n }}
                        </div>
                        <div class="pt-2 min-w-0">
                            <h3 class="text-lg font-bold text-navy mb-2">{{ step.title }}</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>
