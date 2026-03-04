<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Profila iestatījumi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">
                Mans Profils
            </h2>
        </template>

        <div class="py-10 bg-slate-200 min-h-screen">
            <div class="mx-auto max-w-5xl space-y-10 px-4 sm:px-6 lg:px-8">
                
                <div class="bg-white shadow-sm border border-slate-200 p-6 rounded-xl overflow-hidden">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-8">
                    <UpdatePasswordForm />
                </div>

                <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-8">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>