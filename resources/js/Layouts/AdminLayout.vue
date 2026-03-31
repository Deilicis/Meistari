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
    WrenchScrewdriverIcon,
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    ExclamationTriangleIcon,
    UsersIcon,
    MagnifyingGlassIcon,
    ChevronDownIcon,
    Bars3Icon,
    XMarkIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

const page = usePage<{
    auth: { user: AuthUser };
    flash: { success?: string; error?: string; info?: string };
}>();
const user = computed(() => page.props.auth.user);

defineSlots<{
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

const isAdmin = computed(() => user.value?.roles.includes('admin') ?? false);

const roleBadgeClass = computed(() =>
    isAdmin.value ? 'bg-red-500 text-white' : 'bg-orange-400 text-white'
);
const roleLabel = computed(() => isAdmin.value ? 'Admins' : 'Moderators');

const navLinkClass = (active: boolean) =>
    active
        ? 'inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold text-white bg-white/10 transition-colors'
        : 'inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium text-white/60 hover:text-white hover:bg-white/10 transition-colors';

const mobileNavLinkClass = (active: boolean) =>
    'flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors ' +
    (active ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10');
</script>

<template>
    <div class="min-h-screen bg-gray-100 font-sans">
        <nav class="bg-navy relative z-20">
            <div class="h-0.5 w-full bg-red-500" />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-15 py-2.5 justify-between items-center gap-4">

                    <!-- Left side -->
                    <div class="flex items-center gap-4 min-w-0 flex-1">
                        <Link :href="route('admin.dashboard')" class="flex items-center gap-2.5 shrink-0">
                            <ApplicationLogo class="block h-8 w-auto object-contain brightness-0 invert" />
                            <span class="text-lg font-extrabold tracking-widest uppercase text-white hidden lg:block">
                                Meistari
                            </span>
                        </Link>

                        <!-- Navigation -->
                        <div class="hidden sm:flex items-center gap-0.5 overflow-x-auto">
                            <Link
                                :href="route('admin.dashboard')"
                                :class="navLinkClass(route().current('admin.dashboard'))"
                            >
                                <HomeIcon class="w-3 h-3" />
                                Panelis
                            </Link>

                            <Link
                                :href="route('admin.complaints.index')"
                                :class="navLinkClass(route().current('admin.complaints.*'))"
                            >
                                <ExclamationTriangleIcon class="w-3 h-3" />
                                Sūdzības
                            </Link>

                            <template v-if="isAdmin">
                                <Link
                                    :href="route('admin.seekers.index')"
                                    :class="navLinkClass(route().current('admin.seekers.*'))"
                                >
                                    <MagnifyingGlassIcon class="w-3 h-3" />
                                    Meklētāji
                                </Link>

                                <Link
                                    :href="route('admin.masters.index')"
                                    :class="navLinkClass(route().current('admin.masters.*'))"
                                >
                                    <WrenchScrewdriverIcon class="w-3 h-3" />
                                    Meistari
                                </Link>

                                <Link
                                    :href="route('admin.services.index')"
                                    :class="navLinkClass(route().current('admin.services.*'))"
                                >
                                    <BriefcaseIcon class="w-3 h-3" />
                                    Pakalpojumi
                                </Link>

                                <Link
                                    :href="route('admin.job-requests.index')"
                                    :class="navLinkClass(route().current('admin.job-requests.*'))"
                                >
                                    <ClipboardDocumentListIcon class="w-3 h-3" />
                                    Sludinājumi
                                </Link>

                                <Link
                                    :href="route('admin.staff.index')"
                                    :class="navLinkClass(route().current('admin.staff.*'))"
                                >
                                    <UsersIcon class="w-3 h-3" />
                                    Darbinieki
                                </Link>

                                <Link
                                    :href="route('admin.audit-logs.index')"
                                    :class="navLinkClass(route().current('admin.audit-logs.*'))"
                                >
                                    <ClockIcon class="w-3 h-3" />
                                    Audits
                                </Link>
                            </template>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="hidden sm:flex items-center gap-3 shrink-0">
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full" :class="roleBadgeClass">
                            {{ roleLabel }}
                        </span>

                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors focus:outline-none"
                                >
                                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-red-500 text-white">
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

                    <!-- Hamburger -->
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

            <!-- Mobile nav -->
            <div
                :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                class="sm:hidden border-t border-white/10"
            >
                <div class="px-3 py-2 space-y-1">
                    <Link :href="route('admin.dashboard')"
                        :class="mobileNavLinkClass(route().current('admin.dashboard'))"
                        @click="showingNavigationDropdown = false">
                        <HomeIcon class="w-4 h-4" />
                        Panelis
                    </Link>

                    <Link :href="route('admin.complaints.index')"
                        :class="mobileNavLinkClass(route().current('admin.complaints.*'))"
                        @click="showingNavigationDropdown = false">
                        <ExclamationTriangleIcon class="w-4 h-4" />
                        Sūdzības
                    </Link>

                    <template v-if="isAdmin">
                        <Link :href="route('admin.seekers.index')"
                            :class="mobileNavLinkClass(route().current('admin.seekers.*'))"
                            @click="showingNavigationDropdown = false">
                            <MagnifyingGlassIcon class="w-4 h-4" />
                            Meklētāji
                        </Link>

                        <Link :href="route('admin.masters.index')"
                            :class="mobileNavLinkClass(route().current('admin.masters.*'))"
                            @click="showingNavigationDropdown = false">
                            <WrenchScrewdriverIcon class="w-4 h-4" />
                            Meistari
                        </Link>

                        <Link :href="route('admin.services.index')"
                            :class="mobileNavLinkClass(route().current('admin.services.*'))"
                            @click="showingNavigationDropdown = false">
                            <BriefcaseIcon class="w-4 h-4" />
                            Pakalpojumi
                        </Link>

                        <Link :href="route('admin.job-requests.index')"
                            :class="mobileNavLinkClass(route().current('admin.job-requests.*'))"
                            @click="showingNavigationDropdown = false">
                            <ClipboardDocumentListIcon class="w-4 h-4" />
                            Sludinājumi
                        </Link>

                        <Link :href="route('admin.staff.index')"
                            :class="mobileNavLinkClass(route().current('admin.staff.*'))"
                            @click="showingNavigationDropdown = false">
                            <UsersIcon class="w-4 h-4" />
                            Darbinieki
                        </Link>

                        <Link :href="route('admin.audit-logs.index')"
                            :class="mobileNavLinkClass(route().current('admin.audit-logs.*'))"
                            @click="showingNavigationDropdown = false">
                            <ClockIcon class="w-4 h-4" />
                            Audits
                        </Link>
                    </template>
                </div>

                <div class="border-t border-white/10 px-4 py-3">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0 bg-red-500 text-white">
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

        <main>
            <slot />
        </main>
    </div>

    <Toaster richColors position="bottom-right" />
</template>
