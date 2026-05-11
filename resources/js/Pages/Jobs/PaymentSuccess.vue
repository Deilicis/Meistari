<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps<{ job_id: number }>();
</script>

<template>
    <Head :title="t('jobs.payment_success_title')" />

    <AuthenticatedLayout>
        <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
            <div class="text-center max-w-md">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-100 mb-6">
                    <CheckCircleIcon class="w-12 h-12 text-emerald-500" />
                </div>

                <h1 class="text-2xl font-extrabold text-navy mb-3">{{ t('jobs.payment_success_title') }}</h1>

                <p class="text-gray-600 leading-relaxed mb-2">
                    {{ t('jobs.payment_success_desc1') }}
                </p>
                <p class="text-sm text-gray-400 mb-8">
                    {{ t('jobs.payment_success_desc2') }}
                </p>

                <Link
                    :href="route('jobs.show', props.job_id)"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-navy text-white text-sm font-semibold rounded-xl hover:bg-navy/90 transition-colors"
                >
                    {{ t('jobs.payment_success_back') }}
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
