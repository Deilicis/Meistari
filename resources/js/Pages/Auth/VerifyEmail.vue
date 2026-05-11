<script setup lang="ts">
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
        <Head :title="t('auth.verify_email.title')" />

        <div class="mb-8 text-center md:text-left">
            <h2 class="text-3xl font-bold text-gray-900">{{ t('auth.verify_email.title') }}</h2>
            <p class="text-sm text-gray-500 mt-4 leading-relaxed">{{ t('auth.verify_email.desc1') }}</p>
            <p class="text-sm text-gray-500 mt-2">{{ t('auth.verify_email.desc2') }}</p>
        </div>

        <div
            class="mb-6 text-sm font-medium text-green-600 bg-green-50 p-4 rounded-lg border border-green-200"
            v-if="verificationLinkSent"
        >
            {{ t('auth.verify_email.link_sent') }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-4">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-gray-600 hover:text-gray-900 underline font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    {{ t('auth.verify_email.logout') }}
                </Link>

                <PrimaryButton
                    class="w-full sm:w-auto bg-[#0a192f] hover:bg-blue-900 px-8 py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ t('auth.verify_email.resend') }}
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>