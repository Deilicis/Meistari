<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:flex-row">
        <div class="sm:w-1/2 w-full bg-blue-900 flex flex-col justify-center items-center p-12 text-white relative">
            <div class="absolute inset-0 overflow-hidden opacity-10">
                <svg class="absolute left-[20%] top-[10%] w-[1000px] h-[1000px]" fill="currentColor" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"/></svg>
            </div>

            <div class="relative z-10 flex flex-col items-center">
                <Link href="/">
                    <img src="/logo.png" alt="Meistari Logo" class="w-32 h-32 object-contain mb-6 drop-shadow-lg" />
                </Link>
                
                <h1 class="text-5xl font-extrabold tracking-widest uppercase mb-4 text-white drop-shadow-md">
                    Meistari
                </h1>
                
                <p class="text-lg text-blue-200 text-center max-w-sm mt-2 leading-relaxed">
                    Tavs uzticamais partneris. Atrodi labākos meistarus un realizē savus projektus kvalitatīvi un droši.
                </p>
            </div>
        </div>

        <div class="sm:w-1/2 w-full flex items-center justify-center p-6 sm:p-12 bg-gray-50">
            <div class="w-full max-w-md bg-white p-8 sm:p-10 shadow-2xl rounded-2xl border border-gray-100">
                <slot />
            </div>
        </div>
    </div>
</template>