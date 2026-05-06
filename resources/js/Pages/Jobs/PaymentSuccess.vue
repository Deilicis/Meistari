<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{ job_id: number }>();
</script>

<template>
    <Head title="Maksājums veiksmīgs" />

    <AuthenticatedLayout>
        <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
            <div class="text-center max-w-md">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-100 mb-6">
                    <CheckCircleIcon class="w-12 h-12 text-emerald-500" />
                </div>

                <h1 class="text-2xl font-extrabold text-navy mb-3">Maksājums veiksmīgs!</h1>

                <p class="text-gray-600 leading-relaxed mb-2">
                    Paldies par maksājumu. Meistars tiks informēts un varēs sākt darbu.
                </p>
                <p class="text-sm text-gray-400 mb-8">
                    Darba statuss var atjaunoties dažu sekunžu laikā.
                </p>

                <Link
                    :href="route('seeker.job-requests.show', props.job_id)"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-navy text-white text-sm font-semibold rounded-xl hover:bg-navy/90 transition-colors"
                >
                    Atgriezties uz darbu
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
