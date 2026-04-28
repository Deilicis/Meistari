<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import WelcomeNav from '@/Components/Welcome/WelcomeNav.vue';
import WelcomeFooter from '@/Components/Welcome/WelcomeFooter.vue';
import { computed } from 'vue';

const page = usePage();
const isAuth = computed(() => !!(page.props as any).auth?.user);
</script>

<template>
    <Head title="Privātuma politika — Meistari" />

    <div class="min-h-screen flex flex-col font-sans bg-white">
        <WelcomeNav :isAuth="isAuth" />

        <!-- Hero -->
        <div class="bg-navy text-white py-14">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Privātuma politika</h1>
                <p class="mt-3 text-white/50 text-sm">Kā mēs apstrādājam jūsu datus</p>
            </div>
        </div>

        <!-- Content -->
        <main class="flex-grow py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">1. Pārzinis</h2>
                    <p class="text-slate-600 leading-relaxed">SIA "Meistari", reģistrācijas numurs: 40000000000, juridiskā adrese: Rīga, Latvija. Kontaktpersona datu aizsardzības jautājumos: <a href="mailto:privacy@meistari.lv" class="text-navy font-medium hover:underline">privacy@meistari.lv</a></p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">2. Kādus datus mēs vācam</h2>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                        <li><span class="font-medium text-slate-700">Reģistrācijas dati</span> — vārds, e-pasta adrese, tālruņa numurs</li>
                        <li><span class="font-medium text-slate-700">Profila informācija</span> — atrašanās vieta, darba pieredze, fotoattēli, pakalpojumu apraksti</li>
                        <li><span class="font-medium text-slate-700">Lietošanas dati</span> — apmeklētās lapas, darbības platformā, sesijas dati</li>
                        <li><span class="font-medium text-slate-700">Saziņas dati</span> — ziņojumi, kas apmainīti starp lietotājiem platformā</li>
                    </ul>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">3. Kāpēc mēs apstrādājam datus</h2>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                        <li>Konta izveidošanai, autentifikācijai un pārvaldībai</li>
                        <li>Platformas pamata pakalpojumu nodrošināšanai</li>
                        <li>Saziņas nodrošināšanai starp lietotājiem</li>
                        <li>Platformas darbības uzlabošanai un drošības nodrošināšanai</li>
                        <li>Likumisku pienākumu izpildei</li>
                    </ul>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">4. Datu glabāšana</h2>
                    <p class="text-slate-600 leading-relaxed">Dati tiek glabāti ES serveriem atbilstošos datu centros, kas atrodas Eiropas Savienībā. Konta dati tiek dzēsti 30 dienu laikā pēc konta dzēšanas pieprasījuma saņemšanas, ja vien to ilgāku glabāšanu nepieprasa piemērojamie normatīvie akti.</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">5. Jūsu tiesības (VDAR)</h2>
                    <p class="text-slate-600 leading-relaxed mb-3">Saskaņā ar Vispārīgo datu aizsardzības regulu (VDAR) jums ir tiesības:</p>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                        <li>Piekļūt saviem personas datiem</li>
                        <li>Labot neprecīzus vai nepilnīgus datus</li>
                        <li>Dzēst savus datus ("tiesības tikt aizmirstam")</li>
                        <li>Ierobežot datu apstrādi</li>
                        <li>Saņemt savus datus strukturētā formātā (datu pārnesamība)</li>
                    </ul>
                    <p class="text-slate-600 leading-relaxed mt-3">Lai izmantotu šīs tiesības, lūdzam rakstīt uz <a href="mailto:privacy@meistari.lv" class="text-navy font-medium hover:underline">privacy@meistari.lv</a></p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">6. Sīkdatnes</h2>
                    <p class="text-slate-600 leading-relaxed">Platforma izmanto tehniskās sīkdatnes sesijas uzturēšanai un lietotāja preferenču saglabāšanai. Šīs sīkdatnes ir nepieciešamas platformas pareizai darbībai un nav iespējams atteikties no to izmantošanas. Analītikas sīkdatnes tiek izmantotas tikai ar lietotāja skaidru piekrišanu.</p>
                </section>

                <div class="border-t border-slate-200 my-8" />

                <section>
                    <h2 class="text-xl font-semibold text-navy mb-3">7. Izmaiņas politikā</h2>
                    <p class="text-slate-600 leading-relaxed">Par būtiskām izmaiņām privātuma politikā lietotāji tiks informēti e-pastā vismaz 14 dienas pirms to stāšanās spēkā. Jaunākā politikas versija vienmēr ir pieejama šajā lapā.</p>
                </section>

            </div>
        </main>

        <WelcomeFooter />
    </div>
</template>
