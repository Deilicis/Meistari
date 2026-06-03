<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SeekerDashboard from './DashboardPartials/SeekerDashboard.vue';
import MasterDashboard from './DashboardPartials/MasterDashboard.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useActiveRole } from '@/composables/useActiveRole';
import { BriefcaseIcon, ClipboardDocumentListIcon, PlusIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import type { AuthUser, Service, JobRequest } from '@/types/models';

const { t } = useI18n();

const props = defineProps<{
    stats: Record<string, number>;
    recentServices: Service[];
    recentJobRequests: JobRequest[];
    recentApplications: any[];
    recentApplicationsReceived: any[];
}>();

const page = usePage<{ auth: { user: AuthUser } }>();
const user = computed(() => page.props.auth.user);

const { isMasterActive } = useActiveRole();
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1" :class="isMasterActive ? 'bg-gold' : 'bg-emerald-400'" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <BriefcaseIcon v-if="isMasterActive" class="w-6 h-6 text-gold" />
                        <ClipboardDocumentListIcon v-else class="w-6 h-6 text-emerald-400" />
                        <div>
                            <h1 class="text-2xl font-extrabold text-white tracking-tight">
                                {{ isMasterActive ? t('dashboard.master.panel_label') : t('dashboard.seeker.panel_label') }}
                            </h1>
                            <p class="text-white/50 text-sm mt-0.5">
                                <span class="font-semibold" :class="isMasterActive ? 'text-gold' : 'text-emerald-400'">
                                    {{ isMasterActive
                                        ? t('services.my_count', props.stats.total_services ?? 0)
                                        : t('jobs.my_count', props.stats.total_job_requests ?? 0) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3 shrink-0">
                        <template v-if="isMasterActive">
                            <Link
                                :href="route('master.services.index')"
                                class="inline-flex items-center gap-2 bg-gold text-navy text-sm font-bold px-4 py-2 rounded-xl hover:bg-yellow-400 transition-colors"
                            >
                                <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                                {{ t('dashboard.master.add_service') }}
                            </Link>
                            <Link
                                :href="route('master.job-requests.categories')"
                                class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-white/20 transition-colors"
                            >
                                {{ t('dashboard.master.find_jobs') }}
                                <ArrowRightIcon class="w-4 h-4" />
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('seeker.job-requests.index')"
                                class="inline-flex items-center gap-2 bg-emerald-400 text-white text-sm font-bold px-4 py-2 rounded-xl hover:bg-emerald-500 transition-colors"
                            >
                                <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                                {{ t('dashboard.seeker.create_listing') }}
                            </Link>
                            <Link
                                :href="route('seeker.services.index')"
                                class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-white/20 transition-colors"
                            >
                                {{ t('dashboard.seeker.find_masters') }}
                                <ArrowRightIcon class="w-4 h-4" />
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <MasterDashboard
                    v-if="isMasterActive"
                    :stats="props.stats"
                    :recentServices="props.recentServices"
                    :recentApplications="props.recentApplications"
                />
                <SeekerDashboard
                    v-else-if="!isMasterActive"
                    :stats="props.stats"
                    :user="user"
                    :recentJobRequests="props.recentJobRequests"
                    :recentApplicationsReceived="props.recentApplicationsReceived"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>