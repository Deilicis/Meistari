<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
import { ChevronDownIcon } from '@heroicons/vue/24/outline';
import { useActiveRole } from '@/composables/useActiveRole';
import { useEscapeKey } from '@/composables/useEscapeKey';
import { useClickOutside } from '@/composables/useClickOutside';
import RoleSwitcher from '@/Components/Navbar/RoleSwitcher.vue';
import AddRoleModal from '@/Components/Navbar/AddRoleModal.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import type { AuthUser, Profile } from '@/types/models';

type LayoutUser = AuthUser & { profile?: Profile };

const props = defineProps<{ user: LayoutUser }>();

const { accentBgClass, roleLabel, hasBothRoles, isMaster, isSeeker } = useActiveRole();

const open = ref(false);
const addRoleModal = ref<'master' | 'seeker' | null>(null);
const container = ref<HTMLElement | null>(null);

useEscapeKey(() => { open.value = false; }, () => open.value);
useClickOutside(container, () => { open.value = false; });
</script>

<template>
    <div ref="container" class="relative">
        <button
            @click="open = !open"
            :aria-expanded="open"
            aria-haspopup="menu"
            :aria-label="t('nav.profile_menu_label', { name: user.name })"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-2 focus-visible:ring-offset-navy"
        >
            <img
                v-if="user.profile?.avatar"
                :src="`/storage/${user.profile.avatar}`"
                :alt="t('common.avatar_alt', { name: user.name })"
                class="w-7 h-7 rounded-full object-cover border border-white/20 shrink-0"
            />
            <span
                v-else
                class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                :class="accentBgClass"
                aria-hidden="true"
            >
                {{ user.name.charAt(0).toUpperCase() }}
            </span>
            <span class="hidden lg:block">{{ user.name }}</span>
            <ChevronDownIcon class="h-3.5 w-3.5 text-white/50" aria-hidden="true" />
        </button>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                role="menu"
                class="absolute right-0 top-full mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden"
            >
                
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <img
                            v-if="user.profile?.avatar"
                            :src="`/storage/${user.profile.avatar}`"
                            :alt="t('common.avatar_alt', { name: user.name })"
                            class="w-9 h-9 rounded-full object-cover border border-gray-200 shrink-0"
                        />
                        <span
                            v-else
                            class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                            :class="accentBgClass"
                            aria-hidden="true"
                        >
                            {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-800 truncate">{{ user.name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                        </div>
                    </div>
                    <span
                        class="mt-2 inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full"
                        :class="accentBgClass"
                    >
                        {{ roleLabel }}
                    </span>
                </div>

                
                <RoleSwitcher v-if="hasBothRoles" @switched="open = false" />

                
                <div v-else class="px-4 py-3 border-t border-gray-100">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ t('nav.add_role') }}</p>
                    <button
                        v-if="!isMaster"
                        @click="addRoleModal = 'master'; open = false"
                        type="button"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-yellow-50 hover:text-yellow-800 transition-colors text-left"
                    >
                        <span class="w-3 h-3 rounded-full bg-yellow-400 flex-shrink-0" />
                        {{ t('nav.add_master_role') }}
                    </button>
                    <button
                        v-if="!isSeeker"
                        @click="addRoleModal = 'seeker'; open = false"
                        type="button"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-800 transition-colors text-left"
                    >
                        <span class="w-3 h-3 rounded-full bg-emerald-400 flex-shrink-0" />
                        {{ t('nav.add_seeker_role') }}
                    </button>
                </div>

                
                <div class="border-t border-gray-100 py-1">
                    <Link
                        :href="route('profile.edit')"
                        role="menuitem"
                        class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                        @click="open = false"
                    >
                        {{ t('nav.my_profile') }}
                    </Link>
                </div>


                <div class="border-t border-gray-100 px-4 py-3 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ t('nav.language') }}</span>
                    <div class="text-gray-700">
                        <LanguageSwitcher />
                    </div>
                </div>


                <div class="border-t border-gray-100 py-1">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        role="menuitem"
                        class="w-full text-left flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                    >
                        {{ t('nav.logout') }}
                    </Link>
                </div>
            </div>
        </Transition>
    </div>

    
    <AddRoleModal
        v-if="addRoleModal"
        :show="addRoleModal !== null"
        :roleToAdd="addRoleModal"
        @close="addRoleModal = null"
    />
</template>
