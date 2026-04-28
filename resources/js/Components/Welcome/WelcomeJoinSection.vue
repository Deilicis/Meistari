<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{ canRegister?: boolean }>();
</script>

<template>
    <section class="py-12 bg-slate-50 border-t border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-navy">Pievienojies mūsu kopienai</h2>
                <p class="text-sm text-gray-500 mt-1">Izvēlies savu lomu un sāc jau šodien</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                <!-- Meistars card -->
                <div class="bg-navy rounded-2xl overflow-hidden flex flex-col">
                    <div class="h-1 bg-gold" />
                    <div class="p-8 flex-grow">
                        <div class="w-12 h-12 rounded-xl bg-gold/20 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-gold text-xs font-bold tracking-widest uppercase mb-2">Esmu meistars</p>
                        <h3 class="text-xl font-extrabold text-white mb-2">Atrodi jaunus klientus</h3>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">Reģistrējies, izveido sava darba profilu un saņem pieprasījumus no klientiem, kas meklē tieši tavus pakalpojumus.</p>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center gap-2 bg-gold text-navy text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-yellow-400 transition-colors"
                        >
                            Sākt kā meistars
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Meklētājs card -->
                <div class="bg-navy rounded-2xl overflow-hidden flex flex-col">
                    <div class="h-1 bg-emerald-400" />
                    <div class="p-8 flex-grow">
                        <div class="w-12 h-12 rounded-xl bg-emerald-400/20 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <p class="text-emerald-400 text-xs font-bold tracking-widest uppercase mb-2">Meklēju meistaru</p>
                        <h3 class="text-xl font-extrabold text-white mb-2">Atrodi īsto speciālistu</h3>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">Publicē sludinājumu, apraksti nepieciešamo darbu un saņem piedāvājumus no pārbaudītiem meistariem tavā apkaimē.</p>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center gap-2 bg-emerald-400 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-emerald-500 transition-colors"
                        >
                            Meklēt meistaru
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>
