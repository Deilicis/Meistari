<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useActiveRole } from '@/composables/useActiveRole';

const { t } = useI18n();
import {
    HomeIcon,
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    MagnifyingGlassIcon,
    InboxArrowDownIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';

const { isMasterActive, isSeekerActive, accentTextClass } = useActiveRole();

const baseLinkClass = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors';

function linkClass(active: boolean) {
    return active
        ? `${baseLinkClass} text-white font-semibold`
        : `${baseLinkClass} text-white/60 hover:text-white hover:bg-white/10`;
}
</script>

<template>
    <div class="flex items-center gap-0.5">
        <Link
            :href="route('dashboard')"
            :class="linkClass(route().current('dashboard'))"
            :aria-current="route().current('dashboard') ? 'page' : undefined"
        >
            <HomeIcon class="w-3.5 h-3.5" aria-hidden="true" />
            {{ t('nav.dashboard') }}
            <span
                v-if="route().current('dashboard')"
                class="absolute bottom-0 left-3 right-3 h-0.5 rounded-full"
                :class="accentTextClass"
            />
        </Link>

        <!-- Master links -->
        <template v-if="isMasterActive">
            <Link
                :href="route('master.services.index')"
                :class="linkClass(route().current('master.services.index'))"
                :aria-current="route().current('master.services.index') ? 'page' : undefined"
            >
                <BriefcaseIcon class="w-3.5 h-3.5" aria-hidden="true" />
                {{ t('nav.my_services') }}
            </Link>

            <Link
                :href="route('master.job-requests.categories')"
                :class="linkClass(route().current('master.job-requests.categories') || route().current('master.job-requests.index'))"
                :aria-current="(route().current('master.job-requests.categories') || route().current('master.job-requests.index')) ? 'page' : undefined"
            >
                <MagnifyingGlassIcon class="w-3.5 h-3.5" aria-hidden="true" />
                {{ t('nav.job_listings') }}
            </Link>

            <Link
                :href="route('master.applications.index')"
                :class="linkClass(route().current('master.applications.index'))"
                :aria-current="route().current('master.applications.index') ? 'page' : undefined"
            >
                <InboxArrowDownIcon class="w-3.5 h-3.5" aria-hidden="true" />
                {{ t('nav.my_applications') }}
            </Link>

            <Link
                :href="route('master.service-applications.index')"
                :class="linkClass(route().current('master.service-applications.*'))"
                :aria-current="route().current('master.service-applications.*') ? 'page' : undefined"
            >
                <InboxArrowDownIcon class="w-3.5 h-3.5" aria-hidden="true" />
                {{ t('nav.service_applications') }}
            </Link>
        </template>

        <!-- Seeker links -->
        <template v-if="isSeekerActive">
            <Link
                :href="route('seeker.categories.index')"
                :class="linkClass(route().current('seeker.categories.index') || route().current('seeker.services.index'))"
                :aria-current="(route().current('seeker.categories.index') || route().current('seeker.services.index')) ? 'page' : undefined"
            >
                <MagnifyingGlassIcon class="w-3.5 h-3.5" aria-hidden="true" />
                {{ t('nav.services') }}
            </Link>

            <Link
                :href="route('seeker.service-applications.index')"
                :class="linkClass(route().current('seeker.service-applications.index'))"
                :aria-current="route().current('seeker.service-applications.index') ? 'page' : undefined"
            >
                <InboxArrowDownIcon class="w-3.5 h-3.5" aria-hidden="true" />
                {{ t('nav.my_applications') }}
            </Link>

            <Link
                :href="route('seeker.job-requests.index')"
                :class="linkClass(route().current('seeker.job-requests.index'))"
                :aria-current="route().current('seeker.job-requests.index') ? 'page' : undefined"
            >
                <ClipboardDocumentListIcon class="w-3.5 h-3.5" aria-hidden="true" />
                {{ t('nav.my_listings') }}
            </Link>

            <Link
                :href="route('seeker.job-applications.index')"
                :class="linkClass(route().current('seeker.job-applications.index'))"
                :aria-current="route().current('seeker.job-applications.index') ? 'page' : undefined"
            >
                <InboxArrowDownIcon class="w-3.5 h-3.5" aria-hidden="true" />
                {{ t('nav.job_applications') }}
            </Link>
        </template>

        <!-- Always visible -->
        <Link
            :href="route('chat.index')"
            :class="linkClass(route().current('chat.*'))"
            :aria-current="route().current('chat.*') ? 'page' : undefined"
        >
            <ChatBubbleLeftRightIcon class="w-3.5 h-3.5" aria-hidden="true" />
            {{ t('nav.messages') }}
        </Link>
    </div>
</template>
