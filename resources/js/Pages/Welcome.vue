<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import TagLink from '@/Components/TagLink.vue';
import TextLink from '@/Components/TextLink.vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="Sākumlapa" />

    <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
        
        <nav class="bg-white border-b border-gray-100 py-4 shadow-sm relative z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <ApplicationLogo class="w-10 h-10 object-contain" />
                    <span class="text-2xl font-extrabold tracking-widest uppercase text-navy">Meistari</span>
                </div>

                <div v-if="canLogin" class="flex items-center gap-5">
                    <Link 
                        v-if="$page.props.auth?.user" 
                        :href="route('dashboard')" 
                        class="text-sm font-semibold text-navy hover:text-blue-600 transition-colors"
                    >
                        Mans Panelis
                    </Link>
                    <template v-else>
                        <TextLink :href="route('login')" class="!text-navy hover:!text-blue-600 text-sm">
                            Ienākt
                        </TextLink>
                        
                        <Link 
                            v-if="canRegister" 
                            :href="route('register')" 
                            class="text-sm font-bold text-white bg-navy hover:bg-navy-hover px-6 py-2.5 rounded-lg transition-colors shadow-md"
                        >
                            Pievienoties
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <main class="flex-grow flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 text-center py-20 relative overflow-hidden">
            
            <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none opacity-[0.03]">
                <div class="absolute -top-[20%] -right-[10%] w-[800px] h-[800px] rounded-full bg-navy"></div>
                <div class="absolute -bottom-[20%] -left-[10%] w-[600px] h-[600px] rounded-full bg-navy"></div>
            </div>

            <div class="relative z-10 w-full max-w-5xl mx-auto">
                <h1 class="text-5xl md:text-6xl font-extrabold text-navy tracking-tight mb-6 leading-tight">
                    Atrodi uzticamu meistaru <br class="hidden sm:block" />
                    <span class="text-blue-600">tavam nākamajam projektam</span>
                </h1>
                <p class="mt-4 text-lg md:text-xl text-gray-500 max-w-2xl mx-auto mb-12">
                    Vairāk nekā 1000+ sertificētu un pārbaudītu meistaru visā Latvijā ir gatavi palīdzēt tieši tagad.
                </p>

                <div class="w-full bg-white p-3 sm:p-4 rounded-2xl shadow-xl flex flex-col sm:flex-row gap-3 border border-gray-100">
                    
                    <div class="flex-1 flex items-center bg-gray-50 rounded-xl px-5 py-3 sm:py-4 border border-transparent focus-within:border-blue-300 focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-50 transition-all">
                        <input 
                            type="text" 
                            placeholder="Kādu pakalpojumu meklē? (piem., Santehniķis)" 
                            class="w-full bg-transparent border-none focus:ring-0 text-gray-800 placeholder-gray-400 p-0 pr-1 sm:text-m" 
                        />
                    </div>

                    <div class="flex-1 flex items-center bg-gray-50 rounded-xl px-5 py-3 sm:py-4 border border-transparent focus-within:border-blue-300 focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-50 transition-all">
                        <input 
                            type="text" 
                            placeholder="Pilsēta vai novads" 
                            class="w-full bg-transparent border-none focus:ring-0 text-gray-800 placeholder-gray-400 p-0 pr-1 sm:text-m" 
                        />
                    </div>

                    <button class="bg-navy hover:bg-navy-hover text-white font-bold py-4 px-10 rounded-xl transition-all duration-200 shadow-md sm:text-lg tracking-wide">
                        Meklēt
                    </button>
                </div>
                
                <div class="mt-8 flex flex-wrap justify-center items-center gap-3">
                    <span class="font-medium text-gray-400 text-sm mr-2">Populārākie pakalpojumi:</span>
                    <TagLink>Elektriķis</TagLink>
                    <TagLink>Apdares darbi</TagLink>
                    <TagLink>Uzkopšana</TagLink>
                    <TagLink>Mēbeļu montāža</TagLink>
                </div>
            </div>
        </main>

        <footer class="bg-white border-t border-gray-100 py-8 relative z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-400">&copy; 2026 Meistari. Neviena tiesība nav aizsargāta :)</p>
                <div class="flex gap-6 text-sm">
                    <TextLink>Noteikumi</TextLink>
                    <TextLink>Privātuma politika</TextLink>
                    <TextLink>Kontakti</TextLink>
                </div>
            </div>
        </footer>

    </div>
</template>