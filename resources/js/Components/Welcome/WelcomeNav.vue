<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    isAuth: boolean;
}>();
</script>

<template>
    <nav class="bg-navy relative z-20">
        <div class="h-0.5 w-full bg-gold" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
            <Link :href="route('home')" class="flex items-center gap-2.5 hover:opacity-80 transition-opacity">
                <ApplicationLogo class="w-8 h-8 object-contain brightness-0 invert" />
                <span class="text-lg font-extrabold tracking-widest uppercase text-white">Meistari</span>
            </Link>
            <div v-if="canLogin" class="flex items-center gap-3">
                <Link v-if="isAuth" :href="route('dashboard')"
                    class="text-sm font-semibold text-white/80 hover:text-white transition-colors">
                    Mans Panelis →
                </Link>
                <template v-else>
                    <Link :href="route('login')"
                        class="text-sm font-medium text-white/70 hover:text-white transition-colors px-3 py-1.5">
                        Ienākt
                    </Link>
                    <Link v-if="canRegister" :href="route('register')"
                        class="text-sm font-bold text-navy bg-gold hover:bg-yellow-400 px-5 py-2 rounded-lg transition-colors">
                        Reģistrēties
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>
