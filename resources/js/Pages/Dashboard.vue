<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SeekerDashboard from './DashboardPartials/SeekerDashboard.vue';
import MasterDashboard from './DashboardPartials/MasterDashboard.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { AuthUser, Service, JobRequest } from '@/types/models';

const props = defineProps<{
    stats: Record<string, number>;
    roles: string[];
    recentServices: Service[];
    recentJobRequests: JobRequest[];
    recentApplications: any[];
    recentApplicationsReceived: any[];
}>();

const page = usePage<{ auth: { user: AuthUser } }>();
const user = computed(() => page.props.auth.user);

const isMaster = computed(() => props.roles?.includes('master') ?? false);
</script>

<template>
    <Head title="Mans Panelis" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-navy">Mans Panelis</h2>
                <p class="text-sm text-gray-500 mt-0.5">Laipni lūdzam atpakaļ, {{ user.name }}!</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <MasterDashboard
                    v-if="isMaster"
                    :stats="props.stats"
                    :user="user"
                    :recentServices="props.recentServices"
                    :recentApplications="props.recentApplications"
                />
                <SeekerDashboard
                    v-else
                    :stats="props.stats"
                    :user="user"
                    :recentJobRequests="props.recentJobRequests"
                    :recentApplicationsReceived="props.recentApplicationsReceived"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>