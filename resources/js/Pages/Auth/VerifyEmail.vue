<script setup lang="ts">
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout>
        <Head title="E-pasta verifikācija" />

        <div class="mb-8 text-center md:text-left">
            <h2 class="text-3xl font-bold text-gray-900">Apstipriniet e-pastu</h2>
            <p class="text-sm text-gray-500 mt-4 leading-relaxed">
                Paldies par pievienošanos platformai! Lai nodrošinātu pilnu piekļuvi visām funkcijām, lūdzu, apstipriniet savu e-pasta adresi, noklikšķinot uz saites, ko tikko Jums nosūtījām.
            </p>
            <p class="text-sm text-gray-500 mt-2">
                Ja nesaņēmāt e-pastu, mēs ar prieku nosūtīsim to vēlreiz. Pārbaudiet arī "Spam" vai "Mēstules" mapi.
            </p>
        </div>

        <div
            class="mb-6 text-sm font-medium text-green-600 bg-green-50 p-4 rounded-lg border border-green-200"
            v-if="verificationLinkSent"
        >
            Jauna verifikācijas saite tika veiksmīgi nosūtīta uz Jūsu norādīto e-pasta adresi!
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-4">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-gray-600 hover:text-gray-900 underline font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Iziet no sistēmas
                </Link>

                <PrimaryButton
                    class="w-full sm:w-auto bg-[#0a192f] hover:bg-blue-900 px-8 py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Nosūtīt saiti vēlreiz
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>