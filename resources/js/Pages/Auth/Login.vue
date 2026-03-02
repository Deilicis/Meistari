<script setup lang="ts">
import Checkbox from '@/Components/Form/Checkbox.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Ienākt" />

        <div class="mb-8 text-center md:text-left">
            <h2 class="text-3xl font-bold text-gray-900">Sveicināti atpakaļ!</h2>
            <p class="text-sm text-gray-500 mt-2">Ievadiet savus datus, lai piekļūtu savam kontam.</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="E-pasts" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autofocus
                    autocomplete="username"
                    placeholder="tavs@epasts.lv"
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <InputLabel for="password" value="Parole" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors"
                    >
                        Aizmirsāt paroli?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full"
                    v-model="form.password"
                    autocomplete="current-password"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="block">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" class="text-[#0a192f] focus:ring-[#0a192f]" />
                    <span class="ms-2 text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Atcerēties mani</span>
                </label>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
                <Link
                    :href="route('register')"
                    class="text-sm text-[#0a192f] hover:text-blue-700 font-semibold underline underline-offset-4"
                >
                    Izveidot jaunu kontu
                </Link>

                <PrimaryButton
                    class="w-full sm:w-auto bg-[#0a192f] hover:bg-blue-900 px-8 py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Ienākt
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>