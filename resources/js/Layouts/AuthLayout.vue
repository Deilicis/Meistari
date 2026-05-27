<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { CheckIcon, ChevronLeftIcon } from '@heroicons/vue/24/outline';

const { t } = useI18n();

const features = computed(() => [
    t('auth.side_feature_1'),
    t('auth.side_feature_2'),
    t('auth.side_feature_3'),
]);
</script>

<template>
    <div class="min-h-screen flex flex-col md:flex-row bg-white">

        <div class="md:w-[45%] lg:w-[40%] w-full bg-navy flex flex-col justify-between p-8 lg:p-14 text-white relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gold" />

            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-20 -left-20 w-80 h-80 rounded-full bg-white/[0.02]"></div>
                <div class="absolute -bottom-16 -right-16 w-64 h-64 rounded-full bg-gold/5"></div>
            </div>

            <div class="relative z-10">
                <Link href="/" class="flex items-center gap-3 mb-12">
                    <ApplicationLogo class="w-9 h-9 brightness-0 invert" />
                    <span class="text-xl font-extrabold tracking-widest uppercase text-white">Meistari</span>
                </Link>

                <div class="mb-10">
                    <h2 class="text-3xl lg:text-4xl font-extrabold leading-tight text-white mb-4">
                        {{ t('auth.side_heading') }}
                    </h2>
                    <p class="text-white/50 text-sm leading-relaxed">
                        {{ t('auth.side_subtitle') }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div v-for="item in features" :key="item" class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-gold/20 border border-gold/40 flex items-center justify-center shrink-0 mt-0.5">
                            <CheckIcon class="w-3 h-3 text-gold" stroke-width="3" />
                        </div>
                        <span class="text-sm text-white/70">{{ item }}</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 mt-10 flex items-center justify-between">
                <Link href="/" class="inline-flex items-center gap-1.5 text-xs text-white/30 hover:text-white/60 transition-colors">
                    <ChevronLeftIcon class="w-3.5 h-3.5" />
                    {{ t('auth.back_to_home') }}
                </Link>
                <LanguageSwitcher />
            </div>
        </div>

        <div class="md:w-[55%] lg:w-[60%] w-full bg-white flex items-center justify-center p-6 sm:p-12 overflow-y-auto">
            <div class="w-full max-w-lg">
                <slot />
            </div>
        </div>
    </div>
</template>
