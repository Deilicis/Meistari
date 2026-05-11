<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import WelcomeNav from '@/Components/Welcome/WelcomeNav.vue';
import WelcomeFooter from '@/Components/Welcome/WelcomeFooter.vue';
import { computed, ref } from 'vue';
import { EnvelopeIcon, WrenchScrewdriverIcon, BuildingOfficeIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

const { t } = useI18n();
const page = usePage();
const isAuth = computed(() => !!(page.props as any).auth?.user);

const faqs = computed(() => [
    { q: t('static.contacts.faq_1_q'), a: t('static.contacts.faq_1_a') },
    { q: t('static.contacts.faq_2_q'), a: t('static.contacts.faq_2_a') },
    { q: t('static.contacts.faq_3_q'), a: t('static.contacts.faq_3_a') },
    { q: t('static.contacts.faq_4_q'), a: t('static.contacts.faq_4_a') },
    { q: t('static.contacts.faq_5_q'), a: t('static.contacts.faq_5_a') },
]);

const openIndex = ref<number | null>(null);
const toggle = (i: number) => {
    openIndex.value = openIndex.value === i ? null : i;
};
</script>

<template>
    <Head :title="t('static.contacts.meta_title')" />

    <div class="min-h-screen flex flex-col font-sans bg-white">
        <WelcomeNav :isAuth="isAuth" />

        <!-- Hero -->
        <div class="bg-navy text-white py-14">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ t('static.contacts.hero_title') }}</h1>
                <p class="mt-3 text-white/50 text-sm">{{ t('static.contacts.hero_subtitle') }}</p>
            </div>
        </div>

        <!-- Content -->
        <main class="flex-grow py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Contact cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-12">

                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-lg bg-navy/5 flex items-center justify-center mb-4">
                            <EnvelopeIcon class="w-5 h-5 text-navy" />
                        </div>
                        <h3 class="text-sm font-bold text-navy mb-1">{{ t('static.contacts.email_card_title') }}</h3>
                        <a href="mailto:info@meistari.lv" class="text-slate-700 font-medium text-sm hover:underline">info@meistari.lv</a>
                        <p class="text-xs text-slate-400 mt-2">{{ t('static.contacts.email_card_note') }}</p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-lg bg-navy/5 flex items-center justify-center mb-4">
                            <WrenchScrewdriverIcon class="w-5 h-5 text-navy" />
                        </div>
                        <h3 class="text-sm font-bold text-navy mb-1">{{ t('static.contacts.support_card_title') }}</h3>
                        <a href="mailto:support@meistari.lv" class="text-slate-700 font-medium text-sm hover:underline">support@meistari.lv</a>
                        <p class="text-xs text-slate-400 mt-2">{{ t('static.contacts.support_card_note') }}</p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-lg bg-navy/5 flex items-center justify-center mb-4">
                            <BuildingOfficeIcon class="w-5 h-5 text-navy" />
                        </div>
                        <h3 class="text-sm font-bold text-navy mb-1">{{ t('static.contacts.office_card_title') }}</h3>
                        <p class="text-slate-700 font-medium text-sm">SIA "Meistari"<br>Rīga, Latvija</p>
                        <p class="text-xs text-slate-400 mt-2">{{ t('static.contacts.office_card_note') }}</p>
                    </div>

                </div>

                <!-- FAQ -->
                <div class="bg-navy rounded-2xl p-8">
                    <h2 class="text-xl font-bold text-white text-center mb-6">{{ t('static.contacts.faq_title') }}</h2>

                    <div class="space-y-2">
                        <div
                            v-for="(faq, i) in faqs"
                            :key="i"
                            class="bg-white/5 hover:bg-white/10 rounded-xl overflow-hidden transition-colors"
                        >
                            <button
                                @click="toggle(i)"
                                class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left"
                            >
                                <span class="text-sm font-semibold text-white">{{ faq.q }}</span>
                                <ChevronDownIcon
                                    class="w-4 h-4 text-white/50 flex-shrink-0 transition-transform duration-200"
                                    :class="openIndex === i ? 'rotate-180' : ''"
                                />
                            </button>
                            <div
                                class="overflow-hidden transition-all duration-200"
                                :style="openIndex === i ? 'max-height: 200px; opacity: 1' : 'max-height: 0; opacity: 0'"
                            >
                                <p class="px-5 pb-4 text-sm text-white/60 leading-relaxed">{{ faq.a }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <WelcomeFooter />
    </div>
</template>
