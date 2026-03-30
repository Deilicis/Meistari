<template>
    <section class="py-16 bg-white border-t border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-navy">Kā tas darbojas?</h2>
                <p class="text-sm text-gray-500 mt-1">Trīs vienkārši soļi, lai sāktu</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="(step, i) in steps"
                    :key="i"
                    class="bg-gray-50 rounded-2xl border border-gray-100 p-6 flex gap-4"
                >
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
</template>

<script setup lang="ts">
const steps = [
    {
        icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        title: 'Publicē sludinājumu',
        desc: 'Apraksti nepieciešamo darbu, norādi budžetu un atrašanās vietu.',
    },
    {
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        title: 'Saņem piedāvājumus',
        desc: 'Meistari piesakās uz tava sludinājumu un nosūta savus piedāvājumus.',
    },
    {
        icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
        title: 'Izvēlies un pieņem',
        desc: 'Salīdzini piedāvājumus, apskata profilus un izvēlies piemērotāko meistaru.',
    },
];
</script>
