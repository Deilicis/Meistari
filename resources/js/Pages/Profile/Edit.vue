<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
}>();
</script>

<template>
    <Head :title="t('profile.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-navy">{{ t('profile.heading') }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ t('profile.subtitle') }}</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-8 px-4 sm:px-6 lg:px-8">

                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
                    <div class="bg-navy px-6 py-4 border-b border-navy-light">
                        <h3 class="text-base font-semibold text-white">{{ t('profile.change_password_title') }}</h3>
                        <p class="text-sm text-white/50 mt-0.5">{{ t('profile.change_password_desc') }}</p>
                    </div>
                    <div class="p-6">
                        <UpdatePasswordForm />
                    </div>
                </div>

                <div class="bg-white shadow-sm border border-red-100 rounded-2xl overflow-hidden">
                    <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <h3 class="text-base font-semibold text-red-700">{{ t('profile.danger_zone_title') }}</h3>
                            <p class="text-sm text-red-500 mt-0.5">{{ t('profile.danger_zone_desc') }}</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <DeleteUserForm />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
