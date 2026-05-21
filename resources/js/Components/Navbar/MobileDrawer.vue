<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import { useActiveRole } from '@/composables/useActiveRole';

const { t } = useI18n();
import AddRoleModal from '@/Components/Navbar/AddRoleModal.vue';
import type { AuthUser, Profile } from '@/types/models';
import {
    HomeIcon,
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    MagnifyingGlassIcon,
    InboxArrowDownIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';

type LayoutUser = AuthUser & { profile?: Profile };

const props = defineProps<{ user: LayoutUser }>();
const emit = defineEmits<{ close: [] }>();

const { isMasterActive, isSeekerActive, hasBothRoles, isMaster, isSeeker, accentBgClass, roleLabel } = useActiveRole();
const addRoleModal = ref<'master' | 'seeker' | null>(null);
const switching = ref(false);

function close() { emit('close'); }

function switchTo(role: 'master' | 'seeker') {
    if (switching.value) return;
    switching.value = true;
    router.post(route('role.switch'), { target_role: role }, {
        onFinish:  () => { switching.value = false; },
        onSuccess: () => close(),
        onError:   () => toast.error(t('nav.role_switch_error')),
    });
}

const linkClass = 'flex items-center gap-2 px-3 py-2.5 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors';
const activeLinkClass = 'flex items-center gap-2 px-3 py-2.5 rounded-md text-sm font-semibold text-white bg-white/10 transition-colors';
</script>

<template>
    <div>
        
        <div class="px-3 py-2 space-y-0.5">
            <Link :href="route('dashboard')" :class="route().current('dashboard') ? activeLinkClass : linkClass"
                :aria-current="route().current('dashboard') ? 'page' : undefined" @click="close">
                <HomeIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.dashboard') }}
            </Link>

            <template v-if="isMasterActive">
                <Link :href="route('master.services.index')" :class="route().current('master.services.index') ? activeLinkClass : linkClass"
                    :aria-current="route().current('master.services.index') ? 'page' : undefined" @click="close">
                    <BriefcaseIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.my_services') }}
                </Link>
                <Link :href="route('master.job-requests.categories')" :class="(route().current('master.job-requests.categories') || route().current('master.job-requests.index')) ? activeLinkClass : linkClass" @click="close">
                    <MagnifyingGlassIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.job_listings') }}
                </Link>
                <Link :href="route('master.applications.index')" :class="route().current('master.applications.index') ? activeLinkClass : linkClass" @click="close">
                    <InboxArrowDownIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.my_applications') }}
                </Link>
                <Link :href="route('master.service-applications.index')" :class="route().current('master.service-applications.*') ? activeLinkClass : linkClass" @click="close">
                    <InboxArrowDownIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.service_applications') }}
                </Link>
            </template>

            <template v-if="isSeekerActive">
                <Link :href="route('seeker.categories.index')" :class="(route().current('seeker.categories.index') || route().current('seeker.services.index')) ? activeLinkClass : linkClass" @click="close">
                    <MagnifyingGlassIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.services') }}
                </Link>
                <Link :href="route('seeker.service-applications.index')" :class="route().current('seeker.service-applications.index') ? activeLinkClass : linkClass" @click="close">
                    <InboxArrowDownIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.my_applications') }}
                </Link>
                <Link :href="route('seeker.job-requests.index')" :class="route().current('seeker.job-requests.index') ? activeLinkClass : linkClass" @click="close">
                    <ClipboardDocumentListIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.my_listings') }}
                </Link>
                <Link :href="route('seeker.job-applications.index')" :class="route().current('seeker.job-applications.index') ? activeLinkClass : linkClass" @click="close">
                    <InboxArrowDownIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.job_applications') }}
                </Link>
            </template>

            <Link :href="route('chat.index')" :class="route().current('chat.*') ? activeLinkClass : linkClass" @click="close">
                <ChatBubbleLeftRightIcon class="w-4 h-4" aria-hidden="true" /> {{ t('nav.messages') }}
            </Link>
        </div>

        
        <div class="border-t border-white/10 px-3 py-3">
            <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-2">{{ t('nav.role_section') }}</p>

            <template v-if="hasBothRoles">
                <button
                    v-if="isMaster"
                    @click="switchTo('master')"
                    :disabled="switching"
                    type="button"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-sm transition-colors disabled:opacity-60"
                    :class="isMasterActive ? 'bg-yellow-400/20 text-yellow-300 font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white'"
                >
                    <span class="w-3 h-3 rounded-full flex-shrink-0" :class="isMasterActive ? 'bg-yellow-400' : 'border-2 border-white/30'" />
                    {{ t('auth.register.role_master') }}
                    <span v-if="isMasterActive" class="ml-auto text-[10px] text-yellow-400 font-bold uppercase">{{ t('nav.active') }}</span>
                </button>
                <button
                    v-if="isSeeker"
                    @click="switchTo('seeker')"
                    :disabled="switching"
                    type="button"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-sm transition-colors disabled:opacity-60"
                    :class="isSeekerActive ? 'bg-emerald-400/20 text-emerald-300 font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white'"
                >
                    <span class="w-3 h-3 rounded-full flex-shrink-0" :class="isSeekerActive ? 'bg-emerald-400' : 'border-2 border-white/30'" />
                    {{ t('auth.register.role_seeker') }}
                    <span v-if="isSeekerActive" class="ml-auto text-[10px] text-emerald-400 font-bold uppercase">{{ t('nav.active') }}</span>
                </button>
            </template>
            <template v-else>
                <div class="px-3 py-1.5">
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full" :class="accentBgClass">
                        {{ roleLabel }}
                    </span>
                </div>
                <button v-if="!isMaster" @click="addRoleModal = 'master'; close()" type="button"
                    class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm text-white/60 hover:text-white hover:bg-white/10 transition-colors text-left">
                    <span class="w-3 h-3 rounded-full bg-yellow-400 flex-shrink-0" />
                    {{ t('nav.add_master_role') }}
                </button>
                <button v-if="!isSeeker" @click="addRoleModal = 'seeker'; close()" type="button"
                    class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm text-white/60 hover:text-white hover:bg-white/10 transition-colors text-left">
                    <span class="w-3 h-3 rounded-full bg-emerald-400 flex-shrink-0" />
                    {{ t('nav.add_seeker_role') }}
                </button>
            </template>
        </div>

        
        <div class="border-t border-white/10 px-4 py-3">
            <div class="flex items-center gap-3 mb-3">
                <img v-if="user.profile?.avatar" :src="`/storage/${user.profile.avatar}`" :alt="t('common.avatar_alt', { name: user.name })"
                    class="w-9 h-9 rounded-full object-cover border border-white/20 shrink-0" />
                <span v-else class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                    :class="accentBgClass" aria-hidden="true">
                    {{ user.name.charAt(0).toUpperCase() }}
                </span>
                <div>
                    <p class="text-sm font-bold text-white">{{ user.name }}</p>
                    <p class="text-xs text-white/50">{{ user.email }}</p>
                </div>
            </div>
            <div class="space-y-0.5">
                <Link :href="route('profile.edit')"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                    @click="close">{{ t('nav.my_profile') }}
                </Link>
                <Link :href="route('logout')" method="post" as="button"
                    class="w-full text-left block px-3 py-2 rounded-md text-sm font-medium text-red-300 hover:text-red-200 hover:bg-white/10 transition-colors"
                    @click="close">{{ t('nav.logout') }}
                </Link>
            </div>
        </div>
    </div>

    <AddRoleModal v-if="addRoleModal" :show="addRoleModal !== null" :roleToAdd="addRoleModal" @close="addRoleModal = null" />
</template>
