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
    <Head :title="t('static.privacy.meta_title')" />

    <div class="min-h-screen flex flex-col font-sans bg-white">
        <WelcomeNav :isAuth="isAuth" />

        
        <div class="bg-navy text-white py-14">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ t('static.privacy.hero_title') }}</h1>
                <p class="mt-3 text-white/50 text-sm">{{ t('static.privacy.hero_subtitle') }}</p>
            </div>
        </div>

        
        <main class="flex-grow py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.privacy.s1_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.privacy.s1_body') }} <a href="mailto:privacy@meistari.lv" class="text-navy font-medium hover:underline">privacy@meistari.lv</a></p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.privacy.s2_heading') }}</h2>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                        <li><span class="font-medium text-slate-700">{{ t('static.privacy.s2_item_1_label') }}</span> - {{ t('static.privacy.s2_item_1_val') }}</li>
                        <li><span class="font-medium text-slate-700">{{ t('static.privacy.s2_item_2_label') }}</span> - {{ t('static.privacy.s2_item_2_val') }}</li>
                        <li><span class="font-medium text-slate-700">{{ t('static.privacy.s2_item_3_label') }}</span> - {{ t('static.privacy.s2_item_3_val') }}</li>
                        <li><span class="font-medium text-slate-700">{{ t('static.privacy.s2_item_4_label') }}</span> - {{ t('static.privacy.s2_item_4_val') }}</li>
                    </ul>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.privacy.s3_heading') }}</h2>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                        <li>{{ t('static.privacy.s3_item_1') }}</li>
                        <li>{{ t('static.privacy.s3_item_2') }}</li>
                        <li>{{ t('static.privacy.s3_item_3') }}</li>
                        <li>{{ t('static.privacy.s3_item_4') }}</li>
                        <li>{{ t('static.privacy.s3_item_5') }}</li>
                    </ul>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.privacy.s4_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.privacy.s4_body') }}</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.privacy.s5_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed mb-3">{{ t('static.privacy.s5_intro') }}</p>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                        <li>{{ t('static.privacy.s5_item_1') }}</li>
                        <li>{{ t('static.privacy.s5_item_2') }}</li>
                        <li>{{ t('static.privacy.s5_item_3') }}</li>
                        <li>{{ t('static.privacy.s5_item_4') }}</li>
                        <li>{{ t('static.privacy.s5_item_5') }}</li>
                    </ul>
                    <p class="text-slate-600 leading-relaxed mt-3">{{ t('static.privacy.s5_contact') }} <a href="mailto:privacy@meistari.lv" class="text-navy font-medium hover:underline">privacy@meistari.lv</a></p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.privacy.s6_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.privacy.s6_body') }}</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">{{ t('static.privacy.s7_heading') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('static.privacy.s7_body') }}</p>
                </section>

            </div>
        </main>

        <WelcomeFooter />
    </div>
</template>
