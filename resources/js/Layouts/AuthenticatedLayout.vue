<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import UserDropdown from '@/Components/Navbar/UserDropdown.vue';
import MainNavLinks from '@/Components/Navbar/MainNavLinks.vue';
import MobileDrawer from '@/Components/Navbar/MobileDrawer.vue';
import { useActiveRole } from '@/composables/useActiveRole';
import type { AuthUser, Profile } from '@/types/models';

type LayoutUser = AuthUser & { profile?: Profile };

const page = usePage<{
    auth: { user: LayoutUser };
    flash: { success?: string; error?: string; info?: string };
}>();
const user = computed(() => page.props.auth.user);

defineSlots<{
    header?: (props: {}) => any;
    default: (props: {}) => any;
}>();

const mobileOpen = ref(false);
const { accentDotClass } = useActiveRole();

const displayToast = () => {
    if (page.props.flash?.success) toast.success(page.props.flash.success);
    if (page.props.flash?.error) toast.error(page.props.flash.error);
    if (page.props.flash?.info) toast.info(page.props.flash.info);
};

onMounted(() => displayToast());
watch(() => page.props.flash, () => displayToast(), { deep: true });
</script>

<template>
    <div class="min-h-screen bg-gray-100 font-sans">
        <nav class="bg-navy relative z-20" aria-label="Galvenā navigācija">
            
            <div class="h-0.5 w-full" :class="accentDotClass" />

            
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-14 items-center justify-between">
                    <Link href="/" class="flex items-center gap-2.5 shrink-0" aria-label="Meistari - sākumlapa">
                        <ApplicationLogo class="block h-8 w-auto object-contain brightness-0 invert" aria-hidden="true" />
                        <span class="text-lg font-extrabold tracking-widest uppercase text-white hidden sm:block">
                            Meistari
                        </span>
                    </Link>

                    
                    <div class="hidden sm:flex items-center gap-2">
                        <NotificationBell :userId="user.id" />
                        <UserDropdown :user="user" />
                    </div>

                    
                    <div class="flex items-center gap-1 sm:hidden">
                        <NotificationBell :userId="user.id" />
                        <button
                            @click="mobileOpen = !mobileOpen"
                            :aria-expanded="mobileOpen"
                            aria-controls="mobile-nav"
                            :aria-label="mobileOpen ? 'Aizvērt navigāciju' : 'Atvērt navigāciju'"
                            class="inline-flex items-center justify-center rounded-md p-2 text-white/60 hover:text-white hover:bg-white/10 transition-colors focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-2 focus-visible:ring-offset-navy"
                        >
                            <XMarkIcon v-if="mobileOpen" class="h-6 w-6" aria-hidden="true" />
                            <Bars3Icon v-else class="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="hidden sm:block border-t border-white/10">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-1">
                    <MainNavLinks />
                </div>
            </div>

            
            <div
                v-if="mobileOpen"
                id="mobile-nav"
                class="sm:hidden border-t border-white/10"
            >
                <MobileDrawer :user="user" @close="mobileOpen = false" />
            </div>
        </nav>

        <header v-if="$slots.header" class="bg-white border-b border-gray-200">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main id="main-content" tabindex="-1">
            <slot />
        </main>
    </div>

    <Toaster richColors position="bottom-right" />
</template>
