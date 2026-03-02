<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Apstiprināt paroli" />

        <div class="mb-8 text-center md:text-left">
            <h2 class="text-3xl font-bold text-gray-900">Drošības pārbaude</h2>
            <p class="text-sm text-gray-500 mt-2">
                Šī ir droša aplikācijas zona. Lūdzu, apstipriniet savu paroli, lai turpinātu darbību.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="password" value="Parole" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="flex justify-end pt-4">
                <PrimaryButton
                    class="w-full sm:w-auto bg-[#0a192f] hover:bg-blue-900 px-8 py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Apstiprināt
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>