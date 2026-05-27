<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useEscapeKey } from '@/composables/useEscapeKey';
import { useClickOutside } from '@/composables/useClickOutside';

const { t } = useI18n();

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    isAuth: boolean;
}>();

const mobileMenuOpen = ref(false);
const navRef = ref<HTMLElement | null>(null);

useEscapeKey(() => { mobileMenuOpen.value = false; }, () => mobileMenuOpen.value);
useClickOutside(navRef, () => { mobileMenuOpen.value = false; });
</script>

<template>
    <nav ref="navRef" class="bg-navy relative z-20">
        <div class="h-0.5 w-full bg-gold" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex justify-between items-center">

                <Link :href="route('home')" class="flex items-center gap-2.5 hover:opacity-80 transition-opacity">
                    <ApplicationLogo class="w-8 h-8 object-contain brightness-0 invert" />
                    <span class="text-lg font-extrabold tracking-widest uppercase text-white">Meistari</span>
                </Link>

                <div class="hidden sm:flex items-center gap-4">
                    <LanguageSwitcher />
                    <div v-if="canLogin" class="flex items-center gap-3">
                        <Link v-if="isAuth" :href="route('dashboard')"
                            class="text-sm font-semibold text-white/80 hover:text-white transition-colors">
                            {{ t('nav.my_panel') }}
                        </Link>
                        <template v-else>
                            <Link :href="route('login')"
                                class="text-sm font-medium text-white/70 hover:text-white transition-colors px-3 py-1.5">
                                {{ t('nav.login') }}
                            </Link>
                            <Link v-if="canRegister" :href="route('register')"
                                class="text-sm font-bold text-navy bg-gold hover:bg-yellow-400 px-5 py-2 rounded-lg transition-colors">
                                {{ t('nav.register') }}
                            </Link>
                        </template>
                    </div>
                </div>

                <button
                    class="sm:hidden text-white p-2 -mr-2 hover:bg-white/10 rounded-lg transition-colors"
                    :aria-expanded="mobileMenuOpen"
                    :aria-label="t('nav.toggle_menu')"
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <XMarkIcon v-if="mobileMenuOpen" class="w-6 h-6" aria-hidden="true" />
                    <Bars3Icon v-else class="w-6 h-6" aria-hidden="true" />
                </button>
            </div>

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="mobileMenuOpen" class="sm:hidden mt-3 pt-3 border-t border-white/10 space-y-1 pb-2">
                    <div class="px-1 py-2">
                        <LanguageSwitcher />
                    </div>

                    <template v-if="canLogin">
                        <Link
                            v-if="isAuth"
                            :href="route('dashboard')"
                            class="block w-full text-left px-3 py-2.5 text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                            @click="mobileMenuOpen = false"
                        >
                            {{ t('nav.my_panel') }}
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="block w-full text-left px-3 py-2.5 text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                                @click="mobileMenuOpen = false"
                            >
                                {{ t('nav.login') }}
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="block w-full text-center px-5 py-3 text-sm font-bold text-navy bg-gold hover:bg-yellow-400 rounded-lg transition-colors mt-1"
                                @click="mobileMenuOpen = false"
                            >
                                {{ t('nav.register') }}
                            </Link>
                        </template>
                    </template>
                </div>
            </Transition>
        </div>
    </nav>
</template>
