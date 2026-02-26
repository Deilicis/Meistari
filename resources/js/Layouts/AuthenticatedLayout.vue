<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

const page = usePage<{ 
    auth: { user: AuthUser }, 
    flash: { success?: string, error?: string, info?: string } 
}>();
const user = computed(() => page.props.auth.user);

defineSlots<{
    header?: (props: {}) => any;
    default: (props: {}) => any;
}>();

const showingNavigationDropdown = ref(false);

const displayToast = () => {
    if (page.props.flash?.success) toast.success(page.props.flash.success);
    if (page.props.flash?.error) toast.error(page.props.flash.error);
    if (page.props.flash?.info) toast.info(page.props.flash.info);
};

onMounted(() => {
    displayToast();
});

watch(
    () => page.props.flash,
    () => {
        displayToast();
    },
    { deep: true }
);

const isMaster = computed(() => user.value?.roles.includes('master') ?? false);
const isSeeker = computed(() => user.value?.roles.includes('seeker') ?? false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-50 font-sans">
            
            <nav class="bg-white border-b border-gray-100 shadow-sm relative z-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center">
                        <div class="flex items-center gap-6">
                            
                            <div class="flex shrink-0 items-center gap-3">
                                <Link :href="route('dashboard')" class="flex items-center gap-3">
                                    <ApplicationLogo class="block h-9 w-auto object-contain" />
                                    <span class="text-xl font-extrabold tracking-widest uppercase text-navy hidden sm:block">Meistari</span>
                                </Link>
                            </div>

                            <div class="hidden sm:flex sm:space-x-6 sm:ms-6">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Mans Panelis
                                </NavLink>
                                
                                </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-lg border border-transparent bg-gray-50 px-4 py-2 text-sm font-bold leading-4 text-navy transition duration-150 ease-in-out hover:bg-gray-100 focus:outline-none"
                                            >
                                                {{ user.name }}

                                                <svg
                                                    class="h-4 w-4 text-gray-500"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400 bg-gray-50 border-b border-gray-100">
                                            Pieslēdzies kā: <span class="font-bold text-gray-600">{{ isMaster ? 'Meistars' : 'Meklētājs' }}</span>
                                        </div>

                                        <DropdownLink :href="route('profile.edit')">
                                            Mans Profils
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Iziet no sistēmas
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden absolute w-full bg-white border-b border-gray-200 z-50 shadow-lg"
                >
                    <div class="space-y-1 p-3">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Mans Panelis
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-gray-200 pb-1 pt-4 bg-gray-50">
                        <div class="px-4">
                            <div class="text-base font-bold text-navy">
                                {{ user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Mans Profils
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Iziet no sistēmas
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white shadow-sm" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>

        <Toaster richColors position="bottom-right" />
    </div>
</template>