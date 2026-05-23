<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    canRegister?: boolean;
}>();
</script>

<template>
    <section class="bg-navy text-white pt-20 pb-28 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-16 right-0 w-[520px] h-[520px] rounded-full bg-gold/[0.04]" />
            <div class="absolute bottom-0 -left-24 w-80 h-80 rounded-full bg-white/[0.02]" />
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 md:gap-8 items-center">

                <div class="md:col-span-7">
                    <div class="border-l-[3px] border-gold/60 pl-6">
                        <h1 class="text-5xl md:text-6xl lg:text-7xl tracking-tight leading-[1.05]">
                            <span class="font-black block">{{ t('welcome.hero.title_line_1') }}</span>
                            <span class="font-light block mt-1 text-white/80">{{ t('welcome.hero.title_line_2') }}</span>
                        </h1>
                    </div>
                    <p class="mt-8 text-lg md:text-xl text-white/70 max-w-xl leading-relaxed pl-6 md:pl-0">
                        {{ t('welcome.hero.subtitle') }}
                    </p>
                </div>

                <div class="md:col-span-5 flex flex-col gap-4">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="group flex flex-col items-start bg-gold hover:bg-yellow-400 text-navy px-8 py-5 rounded-2xl transition-all duration-200 shadow-md hover:shadow-xl hover:scale-[1.02] w-full"
                    >
                        <span class="flex items-center gap-2 text-base md:text-lg font-bold">
                            {{ t('welcome.hero.cta_find') }}
                            <svg
                                class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2.5"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                        <span class="text-sm font-normal text-navy/70 mt-0.5">{{ t('welcome.hero.cta_find_subtitle') }}</span>
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="flex flex-col items-start bg-transparent border-2 border-white/30 hover:border-white hover:bg-white/10 text-white px-8 py-5 rounded-2xl transition-all duration-200 w-full"
                    >
                        <span class="text-base md:text-lg font-bold">{{ t('welcome.hero.cta_post') }}</span>
                        <span class="text-sm font-normal text-white/50 mt-0.5">{{ t('welcome.hero.cta_post_subtitle') }}</span>
                    </Link>

                    <p class="text-xs text-white/30 text-center pt-1">{{ t('welcome.hero.trust_indicator') }}</p>
                </div>

            </div>
        </div>
    </section>
</template>
