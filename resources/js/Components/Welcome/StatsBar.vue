<script setup lang="ts">
import type { WelcomeStats } from '@/types/welcome';

defineProps<{ stats: WelcomeStats }>();
</script>

<template>
    <section class="bg-navy border-t border-white/10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-3">
                <div class="text-center px-4">
                    <div class="text-3xl sm:text-4xl font-extrabold text-gold">{{ stats.masters }}</div>
                    <div class="text-xs sm:text-sm text-white/50 mt-1.5 font-medium">Aktīvi meistari</div>
                </div>
                <div class="text-center px-4 border-x border-white/10">
                    <div class="text-3xl sm:text-4xl font-extrabold text-gold">{{ stats.completed_jobs }}</div>
                    <div class="text-xs sm:text-sm text-white/50 mt-1.5 font-medium">Paveiktie darbi</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-3xl sm:text-4xl font-extrabold text-gold">{{ stats.categories }}</div>
                    <div class="text-xs sm:text-sm text-white/50 mt-1.5 font-medium">Kategorijas</div>
                </div>
            </div>
        </div>
    </section>
</template>
