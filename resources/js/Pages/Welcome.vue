<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="Meistari — Atrodi uzticamu meistaru" />

    <div class="min-h-screen flex flex-col font-sans bg-gray-50">

        <!-- ── Navigācija ── -->
        <nav class="bg-navy relative z-20">
            <div class="h-0.5 w-full bg-gold" />
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
                <div class="flex items-center gap-2.5">
                    <ApplicationLogo class="w-8 h-8 object-contain brightness-0 invert" />
                    <span class="text-lg font-extrabold tracking-widest uppercase text-white">Meistari</span>
                </div>

                <div v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="text-sm font-semibold text-white/80 hover:text-white transition-colors"
                    >
                        Mans Panelis →
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-white/70 hover:text-white transition-colors px-3 py-1.5"
                        >
                            Ienākt
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="text-sm font-bold text-navy bg-gold hover:bg-yellow-400 px-5 py-2 rounded-lg transition-colors"
                        >
                            Reģistrēties
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="bg-navy text-white pb-24 pt-16 relative overflow-hidden">
            <!-- Background shapes -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-white/[0.02]"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-gold/5"></div>
            </div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-block text-gold text-xs font-bold tracking-widest uppercase mb-6 px-4 py-1.5 rounded-full border border-gold/30 bg-gold/5">
                    Latvijas meistaru platforma
                </span>

                <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight mb-6">
                    Atrodi uzticamu meistaru<br class="hidden sm:block" />
                    <span class="text-gold">tavam nākamajam projektam</span>
                </h1>

                <p class="text-lg text-white/60 max-w-2xl mx-auto mb-10">
                    Sertificēti un pārbaudīti meistari visā Latvijā. Publicē sludinājumu un saņem piedāvājumus, vai reģistrējies kā meistars un atrod darbu.
                </p>

                <!-- Search bar -->
                <div class="w-full bg-white/10 backdrop-blur-sm border border-white/10 p-2 rounded-2xl flex flex-col sm:flex-row gap-2 max-w-3xl mx-auto">
                    <div class="flex-1 flex items-center bg-white/10 rounded-xl px-4 py-3 border border-white/10 focus-within:border-gold/50 focus-within:bg-white/15 transition-all">
                        <svg class="w-4 h-4 text-white/40 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            placeholder="Kādu pakalpojumu meklē?"
                            class="w-full bg-transparent border-none focus:ring-0 text-white placeholder-white/40 p-0 text-sm"
                        />
                    </div>
                    <div class="flex-1 flex items-center bg-white/10 rounded-xl px-4 py-3 border border-white/10 focus-within:border-gold/50 focus-within:bg-white/15 transition-all">
                        <svg class="w-4 h-4 text-white/40 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <input
                            type="text"
                            placeholder="Pilsēta vai novads"
                            class="w-full bg-transparent border-none focus:ring-0 text-white placeholder-white/40 p-0 text-sm"
                        />
                    </div>
                    <button class="bg-gold hover:bg-yellow-400 text-navy font-bold py-3 px-8 rounded-xl transition-colors text-sm shrink-0">
                        Meklēt
                    </button>
                </div>

                <!-- Populāri tags -->
                <div class="mt-6 flex flex-wrap justify-center items-center gap-2">
                    <span class="text-white/40 text-xs mr-1">Populāri:</span>
                    <span v-for="tag in ['Elektriķis', 'Santehniķis', 'Apdares darbi', 'Uzkopšana', 'Mēbeļu montāža']" :key="tag"
                        class="text-xs font-medium text-white/60 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 px-3 py-1 rounded-full cursor-pointer transition-colors">
                        {{ tag }}
                    </span>
                </div>
            </div>
        </section>

        <!-- ── Kā tas strādā ── -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-bold text-navy">Kā tas darbojas?</h2>
                    <p class="text-sm text-gray-500 mt-1">Trīs vienkārši soļi, lai sāktu</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="(step, i) in [
                        { icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', title: 'Publicē sludinājumu', desc: 'Apraksti nepieciešamo darbu, norādi budžetu un atrašanās vietu.' },
                        { icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', title: 'Saņem piedāvājumus', desc: 'Meistari piesakās uz tava sludinājumu un nosūta savus piedāvājumus.' },
                        { icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', title: 'Izvēlies un pieņem', desc: 'Salīdzini piedāvājumus, apskata profilus un izvēlies piemērotāko meistaru.' },
                    ]" :key="i"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="step.icon" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gold uppercase tracking-wide mb-1">{{ i + 1 }}. solis</div>
                            <h3 class="text-sm font-bold text-navy mb-1">{{ step.title }}</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Role sadale -->
        <section class="py-5 bg-white border-t border-gray-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-bold text-navy">Pievienojies mūsu kopienai</h2>
                    <p class="text-sm text-gray-500 mt-1">Izvēlies savu lomu un sāc jau šodien</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <!-- Meistaru karte -->
                    <div class="bg-navy rounded-2xl overflow-hidden flex flex-col">
                        <div class="h-1 bg-gold" />
                        <div class="p-8 flex-grow">
                            <div class="w-12 h-12 rounded-xl bg-gold/10 flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-gold text-xs font-bold tracking-widest uppercase mb-2">Esmu meistars</p>
                            <h3 class="text-xl font-extrabold text-white mb-2">Atrodi jaunus klientus</h3>
                            <p class="text-white/50 text-sm leading-relaxed mb-6">
                                Reģistrējies, izveido sava darba profilu un saņem pieprasījumus no klientiem, kas meklē tieši tavus pakalpojumus.
                            </p>
                            <Link v-if="canRegister" :href="route('register')"
                                class="inline-flex items-center gap-2 bg-gold text-navy text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-yellow-400 transition-colors">
                                Sākt kā meistars
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Meklētāju karte -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                        <div class="h-1 bg-emerald-400" />
                        <div class="p-8 flex-grow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p class="text-emerald-500 text-xs font-bold tracking-widest uppercase mb-2">Meklēju meistaru</p>
                            <h3 class="text-xl font-extrabold text-navy mb-2">Atrodi īsto speciālistu</h3>
                            <p class="text-gray-500 text-sm leading-relaxed mb-6">
                                Publicē sludinājumu, apraksti nepieciešamo darbu un saņem piedāvājumus no pārbaudītiem meistariem tavā apkaimē.
                            </p>
                            <Link v-if="canRegister" :href="route('register')"
                                class="inline-flex items-center gap-2 bg-navy text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-navy-hover transition-colors">
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

        <!-- Footer -->
        <footer class="bg-navy border-t border-white/5 py-8 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <ApplicationLogo class="w-6 h-6 brightness-0 invert opacity-60" />
                    <p class="text-sm text-white/30">&copy; 2026 Meistari</p>
                </div>
                <div class="flex gap-6 text-sm text-white/30">
                    <span class="hover:text-white/60 cursor-pointer transition-colors">Noteikumi</span>
                    <span class="hover:text-white/60 cursor-pointer transition-colors">Privātuma politika</span>
                    <span class="hover:text-white/60 cursor-pointer transition-colors">Kontakti</span>
                </div>
            </div>
        </footer>

    </div>
</template>
