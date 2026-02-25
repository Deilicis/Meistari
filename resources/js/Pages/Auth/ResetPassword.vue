<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Izveidot jaunu paroli" />

        <div class="mb-8 text-center md:text-left">
            <h2 class="text-3xl font-bold text-gray-900">Izveidot jaunu paroli</h2>
            <p class="text-sm text-gray-500 mt-2">Ievadiet savu jauno, drošo paroli.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="E-pasts" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-gray-50 text-gray-500 cursor-not-allowed"
                    v-model="form.email"
                    autocomplete="username"
                    readonly
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Jaunā parole" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    autofocus
                    autocomplete="new-password"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Apstiprināt jauno paroli" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                />
                <InputError class="mt-1" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end pt-4">
                <PrimaryButton
                    class="w-full sm:w-auto bg-[#0a192f] hover:bg-blue-900 px-8 py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Atiestatīt paroli
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>