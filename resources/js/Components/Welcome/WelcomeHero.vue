<script setup lang="ts">
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import type { PopularCategory } from '@/types/welcome';

defineProps<{
    popularCategories: PopularCategory[];
    activeTagId: number | null;
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    search: [];
    selectTag: [tag: PopularCategory];
}>();
</script>

<template>
    <section class="bg-navy text-white pb-20 pt-16 relative overflow-hidden">
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

            <div class="w-full bg-white/10 backdrop-blur-sm border border-white/10 p-2 rounded-2xl flex flex-col sm:flex-row gap-2 max-w-3xl mx-auto">
                <div class="flex-1 flex items-center bg-white/10 rounded-xl px-4 py-3 border border-white/10 focus-within:border-gold/50 focus-within:bg-white/15 transition-all">
                    <MagnifyingGlassIcon class="w-4 h-4 text-white/40 mr-3 shrink-0" />
                    <input
                        :value="modelValue"
                        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                        @keydown.enter="emit('search')"
                        type="text"
                        placeholder="Kādu pakalpojumu meklē?"
                        class="w-full bg-transparent border-none focus:ring-0 text-white placeholder-white/40 p-0 text-sm"
                    />
                </div>
                <button
                    @click="emit('search')"
                    class="bg-gold hover:bg-yellow-400 text-navy font-bold py-3 px-8 rounded-xl transition-colors text-sm shrink-0"
                >
                    Meklēt
                </button>
            </div>

            <div class="mt-6 flex flex-wrap justify-center items-center gap-2">
                <span class="text-white/40 text-xs mr-1">Populāri:</span>
                <button
                    v-for="tag in popularCategories"
                    :key="tag.id"
                    @click="emit('selectTag', tag)"
                    class="text-xs font-medium px-3 py-1 rounded-full border transition-colors"
                    :class="tag.id === activeTagId
                        ? 'text-navy bg-gold border-gold font-bold'
                        : 'text-white/60 hover:text-white bg-white/5 hover:bg-white/10 border-white/10 hover:border-white/20'"
                >
                    {{ tag.name }}
                </button>
            </div>
        </div>
    </section>
</template>
