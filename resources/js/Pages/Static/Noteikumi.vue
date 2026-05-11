<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import WelcomeNav from '@/Components/Welcome/WelcomeNav.vue';
import WelcomeFooter from '@/Components/Welcome/WelcomeFooter.vue';
import { computed } from 'vue';

const { t } = useI18n();
const page = usePage();
const isAuth = computed(() => !!(page.props as any).auth?.user);
</script>

<template>
    <Head :title="t('static.terms.meta_title')" />

    <div class="min-h-screen flex flex-col font-sans bg-white">
        <WelcomeNav :isAuth="isAuth" />

        <!-- Hero -->
        <div class="bg-navy text-white py-14">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ t('static.terms.hero_title') }}</h1>
                <p class="mt-3 text-white/50 text-sm">{{ t('static.terms.hero_subtitle') }}</p>
            </div>
        </div>

        <!-- Content -->
        <main class="flex-grow py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.terms.s1_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.terms.s1_body') }}</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.terms.s2_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.terms.s2_body') }}</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.terms.s3_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.terms.s3_body') }}</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.terms.s4_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed mb-3">{{ t('static.terms.s4_intro') }}</p>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                        <li>{{ t('static.terms.s4_item_1') }}</li>
                        <li>{{ t('static.terms.s4_item_2') }}</li>
                        <li>{{ t('static.terms.s4_item_3') }}</li>
                        <li>{{ t('static.terms.s4_item_4') }}</li>
                        <li>{{ t('static.terms.s4_item_5') }}</li>
                    </ul>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.terms.s5_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.terms.s5_body') }}</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.terms.s6_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.terms.s6_body') }}</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.terms.s7_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.terms.s7_body') }} <a href="mailto:info@meistari.lv" class="text-navy font-medium hover:underline">info@meistari.lv</a></p>
                </section>

            </div>
        </main>

        <WelcomeFooter />
    </div>
</template>
