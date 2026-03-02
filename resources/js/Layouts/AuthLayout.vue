<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col md:flex-row bg-white">
        <div class="md:w-[45%] lg:w-[40%] w-full bg-[#0a192f] flex flex-col justify-center items-center p-8 lg:p-16 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-5">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full border-[40px] border-white"></div>
                <div class="absolute bottom-10 -right-10 w-64 h-64 rounded-full border-[20px] border-white"></div>
            </div>

            <div class="relative z-10 flex flex-col items-center text-center">
                <Link href="/">
                    <ApplicationLogo class="w-24 h-24 fill-current text-white mb-6" />
                </Link>
                
                <h1 class="text-4xl lg:text-5xl font-extrabold tracking-widest uppercase mb-4 text-white drop-shadow-md">
                    Meistari
                </h1>
                
                <p class="text-lg text-blue-200 mt-2 max-w-sm font-light leading-relaxed">
                    Atrodi uzticamus meistarus vai piedāvā savus pakalpojumus. Ātri, ērti un droši.
                </p>
            </div>
        </div>

        <div class="md:w-[55%] lg:w-[60%] w-full bg-white flex items-center justify-center p-6 sm:p-12 overflow-y-auto">
            <div class="w-full max-w-lg">
                <slot />
            </div>
        </div>
    </div>
</template>