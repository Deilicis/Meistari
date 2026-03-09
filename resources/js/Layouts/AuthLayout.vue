<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';
import { CheckIcon, ChevronLeftIcon } from '@heroicons/vue/24/outline';
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
                    <span class="inline-block text-gold text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full border border-gold/30 bg-gold/5 mb-5">
                        Latvijas meistaru platforma
                    </span>
                    <h2 class="text-3xl lg:text-4xl font-extrabold leading-tight text-white mb-4">
                        Atrodi uzticamu meistaru vai piedāvā savus pakalpojumus
                    </h2>
                    <p class="text-white/50 text-sm leading-relaxed">
                        Ātri, ērti un droši. Reģistrējies un sāc darboties jau šodien.
                    </p>
                </div>

                <div class="space-y-3">
                    <div v-for="item in [
                        'Sertificēti un pārbaudīti meistari visā Latvijā',
                        'Publicē sludinājumu un saņem piedāvājumus ātri',
                        'Bezmaksas reģistrācija — bez slēptajām maksām',
                    ]" :key="item" class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-gold/20 border border-gold/40 flex items-center justify-center shrink-0 mt-0.5">
                            <CheckIcon class="w-3 h-3 text-gold" stroke-width="3" />
                        </div>
                        <span class="text-sm text-white/70">{{ item }}</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 mt-10">
                <Link href="/" class="inline-flex items-center gap-1.5 text-xs text-white/30 hover:text-white/60 transition-colors">
                    <ChevronLeftIcon class="w-3.5 h-3.5" />
                    Atpakaļ uz sākumlapu
                </Link>
            </div>
        </div>

        <div class="md:w-[55%] lg:w-[60%] w-full bg-white flex items-center justify-center p-6 sm:p-12 overflow-y-auto">
            <div class="w-full max-w-lg">
                <slot />
            </div>
        </div>
    </div>
</template>
