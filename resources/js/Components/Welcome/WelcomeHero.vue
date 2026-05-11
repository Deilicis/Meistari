<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    canRegister?: boolean;
}>();
</script>

<template>
    <section class="bg-navy text-white pb-20 pt-16 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-white/[0.02]" />
            <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-gold/5" />
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-block text-gold text-xs font-bold tracking-widest uppercase mb-6 px-4 py-1.5 rounded-full border border-gold/30 bg-gold/5">
                {{ t('welcome.hero.tagline') }}
            </span>

            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight mb-6">
                {{ t('welcome.hero.title') }}<br class="hidden sm:block" />
                <span class="text-gold">{{ t('welcome.hero.title_accent') }}</span>
            </h1>

            <p class="text-lg text-white/60 max-w-2xl mx-auto mb-10">
                {{ t('welcome.hero.subtitle') }}
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="inline-flex items-center justify-center gap-2 bg-gold hover:bg-yellow-400 text-navy font-bold px-8 py-3.5 rounded-xl transition-colors text-sm"
                >
                    {{ t('welcome.hero.cta_find') }}
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="inline-flex items-center justify-center gap-2 border border-white/30 hover:border-white/60 hover:bg-white/10 text-white font-semibold px-8 py-3.5 rounded-xl transition-colors text-sm"
                >
                    {{ t('welcome.hero.cta_post') }}
                </Link>
            </div>
        </div>
    </section>
</template>
