<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Form/Dropdown.vue';
import DropdownLink from '@/Components/Link/DropdownLink.vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isMaster = computed(() => user.value?.roles?.includes('master') ?? false);
const accentBg = computed(() => isMaster.value ? 'bg-gold text-navy' : 'bg-emerald-400 text-white');
</script>

<template>
    <div class="min-h-screen flex flex-col sm:flex-row relative">
        
        <div v-if="user" class="absolute top-4 right-4 sm:top-6 sm:right-8 z-50">
            <div class="bg-white shadow-md border border-gray-100 rounded-lg px-2 py-1">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition-colors focus:outline-none"
                        >
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                :class="accentBg">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                            <span class="hidden sm:inline-block">{{ user.name }}</span>
                            <ChevronDownIcon class="h-3.5 w-3.5 text-gray-400" />
                        </button>
                    </template>

                    <template #content>
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                            <p class="text-xs text-gray-500">Pieslēdzies kā</p>
                            <p class="text-sm font-bold text-gray-800 truncate">{{ user.name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                        </div>
                        <DropdownLink :href="route('dashboard')">Mans Panelis</DropdownLink>
                        <DropdownLink :href="route('profile.edit')">Mans Profils</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Iziet no sistēmas
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </div>

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

        <div class="sm:w-1/2 w-full flex items-center justify-center p-6 sm:p-12 bg-gray-50 pt-20 sm:pt-12">
            <div class="w-full max-w-md bg-white p-8 sm:p-10 shadow-2xl rounded-2xl border border-gray-100">
                <slot />
            </div>
        </div>
    </div>
</template>