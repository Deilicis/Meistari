<script setup lang="ts">
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';
</script>

<template>
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
</template>
