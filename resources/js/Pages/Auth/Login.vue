<script setup lang="ts">
import Checkbox from '@/Components/Form/Checkbox.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-navy">Sveicināti atpakaļ!</h2>
            <p class="text-sm text-gray-500 mt-1">Ievadiet savus datus, lai piekļūtu savam kontam.</p>
        </div>

        <div v-if="status" class="mb-5 text-sm font-medium text-emerald-700 bg-emerald-50 p-3 rounded-xl border border-emerald-200">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
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
                        class="text-xs font-semibold text-navy hover:text-navy-hover transition-colors"
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

            <label class="flex items-center gap-2 cursor-pointer group">
                <Checkbox name="remember" v-model:checked="form.remember" class="text-navy focus:ring-navy" />
                <span class="text-sm text-gray-500 group-hover:text-gray-700 transition-colors">Atcerēties mani</span>
            </label>

            <div class="pt-2 space-y-3">
                <PrimaryButton
                    class="w-full justify-center py-2.5"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Ienāk...
                    </span>
                    <span v-else>Ienākt</span>
                </PrimaryButton>

                <p class="text-center text-sm text-gray-500">
                    Nav konta?
                    <Link :href="route('register')" class="font-semibold text-navy hover:text-navy-hover underline underline-offset-4 transition-colors">
                        Reģistrēties
                    </Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
