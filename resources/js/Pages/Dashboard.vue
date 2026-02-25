<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SeekerDashboard from './DashboardPartials/SeekerDashboard.vue';
import MasterDashboard from './DashboardPartials/MasterDashboard.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

const page = usePage<{ auth: { user: AuthUser | null } }>();

const user = computed(() => page.props.auth.user);

const isMaster = computed(() => {
    return user.value?.roles.includes('master') ?? false;
});
</script>

<template>
    <Head title="Mans Panelis" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-navy">
                Mans Panelis
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <MasterDashboard v-if="isMaster" />
                <SeekerDashboard v-else />

            </div>
        </div>
    </AuthenticatedLayout>
</template>