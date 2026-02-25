<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout>
        <Head title="Aizmirsāt paroli?" />

        <div class="mb-8 text-center md:text-left">
            <h2 class="text-3xl font-bold text-gray-900">Aizmirsāt paroli?</h2>
            <p class="text-sm text-gray-500 mt-2">
                Nekādu problēmu. Ievadiet savu e-pasta adresi, un mēs nosūtīsim saiti paroles atjaunošanai.
            </p>
        </div>

        <div v-if="status" class="mb-6 text-sm font-medium text-green-600 bg-green-50 p-4 rounded-lg border border-green-200">
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

            <div class="flex items-center justify-end pt-4">
                <PrimaryButton
                    class="w-full sm:w-auto bg-[#0a192f] hover:bg-blue-900 px-8 py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Nosūtīt atjaunošanas saiti
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>