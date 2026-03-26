<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';
import Dropdown from '@/Components/Form/Dropdown.vue';
import DropdownLink from '@/Components/Link/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import type { AuthUser } from '@/types/models';
import {
    HomeIcon,
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    MagnifyingGlassIcon,
    InboxArrowDownIcon,
    ChevronDownIcon,
    Bars3Icon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const page = usePage<{
    auth: { user: AuthUser };
    flash: { success?: string; error?: string; info?: string };
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

onMounted(() => displayToast());
watch(() => page.props.flash, () => displayToast(), { deep: true });

const isMaster = computed(() => user.value?.roles.includes('master') ?? false);
const isSeeker = computed(() => user.value?.roles.includes('seeker') ?? false);
const accentBg = computed(() => isMaster.value ? 'bg-gold text-navy' : 'bg-emerald-400 text-white');
const roleLabel = computed(() => isMaster.value ? 'Meistars' : 'Meklētājs');

const navLinkClass = (active: boolean) =>
    active
        ? `inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-semibold text-white bg-white/10 transition-colors`
        : `inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium text-white/60 hover:text-white hover:bg-white/10 transition-colors`;
</script>

<template>
    <div class="min-h-screen bg-gray-100 font-sans">
        <nav class="bg-navy relative z-20">
            <div class="h-0.5 w-full" :class="isMaster ? 'bg-gold' : 'bg-emerald-400'" />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-15 py-2.5 justify-between items-center">

                    <!-- Kreisā puse -->
                    <div class="flex items-center gap-6">
                        <Link :href="route('dashboard')" class="flex items-center gap-2.5 shrink-0">
                            <ApplicationLogo class="block h-8 w-auto object-contain brightness-0 invert" />
                            <span class="text-lg font-extrabold tracking-widest uppercase text-white hidden sm:block">
                                Meistari
                            </span>
                        </Link>

                        <!-- Navigācija -->
                        <div class="hidden sm:flex items-center gap-1">
                            <Link
                                :href="route('dashboard')"
                                :class="navLinkClass(route().current('dashboard'))"
                            >
                                <HomeIcon class="w-3.5 h-3.5" />
                                Mans Panelis
                            </Link>

                            <Link
                                v-if="isMaster"
                                :href="route('master.services.index')"
                                :class="navLinkClass(route().current('master.services.index'))"
                            >
                                <BriefcaseIcon class="w-3.5 h-3.5" />
                                Mani Pakalpojumi
                            </Link>

                            <Link
                                v-if="isMaster"
                                :href="route('master.job-requests.categories')"
                                :class="navLinkClass(route().current('master.job-requests.categories') || route().current('master.job-requests.index'))"
                            >
                                <MagnifyingGlassIcon class="w-3.5 h-3.5" />
                                Darba Sludinājumi
                            </Link>

                            <Link
                                v-if="isMaster"
                                :href="route('master.applications.index')"
                                :class="navLinkClass(route().current('master.applications.index'))"
                            >
                                <InboxArrowDownIcon class="w-3.5 h-3.5" />
                                Mani Pieteikumi
                            </Link>

                            <Link
                                v-if="isSeeker"
                                :href="route('seeker.categories.index')"
                                :class="navLinkClass(route().current('seeker.categories.index') || route().current('seeker.services.index'))"
                            >
                                <MagnifyingGlassIcon class="w-3.5 h-3.5" />
                                Pakalpojumi
                            </Link>

                            <Link
                                v-if="isSeeker"
                                :href="route('seeker.service-applications.index')"
                                :class="navLinkClass(route().current('seeker.service-applications.index'))"
                            >
                                <InboxArrowDownIcon class="w-3.5 h-3.5" />
                                Mani Pieteikumi
                            </Link>

                            <Link
                                v-if="isSeeker"
                                :href="route('seeker.job-requests.index')"
                                :class="navLinkClass(route().current('seeker.job-requests.index'))"
                            >
                                <ClipboardDocumentListIcon class="w-3.5 h-3.5" />
                                Mani Sludinājumi
                            </Link>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-3">
                        <!-- Loma -->
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full" :class="accentBg">
                            {{ roleLabel }}
                        </span>

                        <!-- Lietotāja izvēlne -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors focus:outline-none"
                                >
                                    <!-- Lietotāja iniciālis -->
                                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                        :class="accentBg">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </span>
                                    {{ user.name }}
                                    <ChevronDownIcon class="h-3.5 w-3.5 text-white/50" />
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                                    <p class="text-xs text-gray-500">Pieslēdzies kā</p>
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ user.name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')">Mans Profils</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Iziet no sistēmas
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Hamburger poga -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-md p-2 text-white/60 hover:text-white hover:bg-white/10 transition-colors focus:outline-none"
                        >
                            <XMarkIcon v-if="showingNavigationDropdown" class="h-6 w-6" />
                            <Bars3Icon v-else class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobilā navigācija -->
            <div
                :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                class="sm:hidden border-t border-white/10"
            >
                <div class="px-3 py-2 space-y-1">
                    <Link :href="route('dashboard')"
                        class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                        :class="{ 'bg-white/10 text-white': route().current('dashboard') }"
                        @click="showingNavigationDropdown = false">
                        <HomeIcon class="w-4 h-4" />
                        Mans Panelis
                    </Link>

                    <Link v-if="isMaster" :href="route('master.services.index')"
                        class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                        :class="{ 'bg-white/10 text-white': route().current('master.services.index') }"
                        @click="showingNavigationDropdown = false">
                        <BriefcaseIcon class="w-4 h-4" />
                        Mani Pakalpojumi
                    </Link>

                    <Link v-if="isMaster" :href="route('master.job-requests.categories')"
                        class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                        :class="{ 'bg-white/10 text-white': route().current('master.job-requests.categories') || route().current('master.job-requests.index') }"
                        @click="showingNavigationDropdown = false">
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        Darba Sludinājumi
                    </Link>

                    <Link v-if="isMaster" :href="route('master.applications.index')"
                        class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                        :class="{ 'bg-white/10 text-white': route().current('master.applications.index') }"
                        @click="showingNavigationDropdown = false">
                        <InboxArrowDownIcon class="w-4 h-4" />
                        Mani Pieteikumi
                    </Link>

                    <Link v-if="isSeeker" :href="route('seeker.categories.index')"
                        class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                        :class="{ 'bg-white/10 text-white': route().current('seeker.categories.index') || route().current('seeker.services.index') }"
                        @click="showingNavigationDropdown = false">
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        Pakalpojumi
                    </Link>

                    <Link v-if="isSeeker" :href="route('seeker.service-applications.index')"
                        class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                        :class="{ 'bg-white/10 text-white': route().current('seeker.service-applications.index') }"
                        @click="showingNavigationDropdown = false">
                        <InboxArrowDownIcon class="w-4 h-4" />
                        Mani Pieteikumi
                    </Link>

                    <Link v-if="isSeeker" :href="route('seeker.job-requests.index')"
                        class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                        :class="{ 'bg-white/10 text-white': route().current('seeker.job-requests.index') }"
                        @click="showingNavigationDropdown = false">
                        <ClipboardDocumentListIcon class="w-4 h-4" />
                        Mani Sludinājumi
                    </Link>
                </div>

                <div class="border-t border-white/10 px-4 py-3">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                            :class="accentBg">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                        <div>
                            <p class="text-sm font-bold text-white">{{ user.name }}</p>
                            <p class="text-xs text-white/50">{{ user.email }}</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Link :href="route('profile.edit')"
                            class="block px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                            @click="showingNavigationDropdown = false">
                            Mans Profils
                        </Link>

                        <Link :href="route('logout')" method="post" as="button"
                            class="w-full text-left block px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                            @click="showingNavigationDropdown = false">
                            Iziet no sistēmas
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Galvene -->
        <header v-if="$slots.header" class="bg-white border-b border-gray-200">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Saturs -->
        <main>
            <slot />
        </main>

    </div>

    <Toaster richColors position="bottom-right" />
</template>