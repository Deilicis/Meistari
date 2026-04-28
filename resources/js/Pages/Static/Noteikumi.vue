<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WelcomeNav from '@/Components/Welcome/WelcomeNav.vue';
import WelcomeFooter from '@/Components/Welcome/WelcomeFooter.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isAuth = computed(() => !!(page.props as any).auth?.user);
</script>

<template>
    <Head title="Lietošanas noteikumi — Meistari" />

    <div class="min-h-screen flex flex-col font-sans bg-white">
        <WelcomeNav :isAuth="isAuth" />

        <!-- Hero -->
        <div class="bg-navy text-white py-14">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Lietošanas noteikumi</h1>
                <p class="mt-3 text-white/50 text-sm">Spēkā no 2026. gada 1. janvāra</p>
            </div>
        </div>

        <!-- Content -->
        <main class="flex-grow py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">1. Vispārīgie noteikumi</h2>
                    <p class="text-slate-600 leading-relaxed">Platforma Meistari nodrošina tiešsaistes vidi, kurā pakalpojumu sniedzēji (Meistari) un klienti (Meklētāji) var savstarpēji sazināties un vienoties par pakalpojumu sniegšanu. Meistari.lv darbojas kā starpnieks un nenes atbildību par darījumiem starp lietotājiem.</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">2. Reģistrācija un konts</h2>
                    <p class="text-slate-600 leading-relaxed">Reģistrējoties lietotājs apliecina, ka ievadītā informācija ir patiesa un precīza. Katrs lietotājs drīkst reģistrēt tikai vienu kontu. Platforma patur tiesības bloķēt vai dzēst kontus, kas pārkāpj šos noteikumus vai tiek izmantoti ļaunprātīgi.</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">3. Pakalpojumu sniedzēju pienākumi</h2>
                    <p class="text-slate-600 leading-relaxed">Meistariem ir jānodrošina, ka viņu profilā norādītā informācija — pieredze, sertifikāti, pakalpojumi un cenas — ir aktuāla un patiesa. Meistars ir personīgi atbildīgs par sniegto pakalpojumu kvalitāti, izpildes termiņiem un atbilstību normatīvajiem aktiem.</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">4. Aizliegtā darbība</h2>
                    <p class="text-slate-600 leading-relaxed mb-3">Platformā ir aizliegts:</p>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                        <li>Ievietot maldinošu vai nepatiesi informāciju</li>
                        <li>Sūtīt nevēlamas vai surogātpasta ziņas citiem lietotājiem</li>
                        <li>Mēģināt apiet platformas maksājumu vai saziņas sistēmu</li>
                        <li>Izmantot automatizētus rīkus, robotus vai skriptus bez rakstiskas atļaujas</li>
                        <li>Publicēt citu lietotāju personīgo informāciju bez viņu piekrišanas</li>
                    </ul>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">5. Atbildības ierobežojums</h2>
                    <p class="text-slate-600 leading-relaxed">Meistari.lv nesniedz garantijas par platformā piedāvāto pakalpojumu kvalitāti vai lietotāju savstarpējo darījumu rezultātiem. Platforma nav atbildīga par tiešiem vai netiešiem zaudējumiem, kas rodas no lietotāju savstarpējiem darījumiem vai platformas darbības pārtraukumiem.</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">6. Izmaiņas noteikumos</h2>
                    <p class="text-slate-600 leading-relaxed">Platforma patur tiesības jebkurā laikā grozīt šos noteikumus. Par būtiskām izmaiņām lietotāji tiks informēti e-pastā vai ar paziņojumu platformā vismaz 14 dienas pirms to stāšanās spēkā. Turpinot izmantot platformu pēc izmaiņu stāšanās spēkā, lietotājs piekrīt jaunajiem noteikumiem.</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">7. Kontakti</h2>
                    <p class="text-slate-600 leading-relaxed">Jautājumu vai neskaidrību gadījumā lūdzam sazināties ar mums: <a href="mailto:info@meistari.lv" class="text-navy font-medium hover:underline">info@meistari.lv</a></p>
                </section>

            </div>
        </main>

        <WelcomeFooter />
    </div>
</template>
