<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';

defineProps<{
    stats?: {
        total_services?: number;
        active_applications?: number;
    }
}>();
</script>

<template>
    <div>
        <h3 class="text-lg font-bold text-gray-900 mb-6">Meistara Panelis</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500 mb-1">Pievienotie Pakalpojumi</div>
                    <div class="text-3xl font-extrabold text-navy">
                        {{ stats?.total_services ?? 0 }}
                    </div>
                </div>
                <div class="mt-6">
                    <Link :href="route('master.services.index')">
                        <PrimaryButton>Pārvaldīt pakalpojumus</PrimaryButton>
                    </Link>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500 mb-1">Aktīvie Darba Pieteikumi</div>
                    <div class="text-3xl font-extrabold text-indigo-600">
                        {{ stats?.active_applications ?? 0 }}
                    </div>
                </div>
                <div class="mt-6">
                    <Link :href="'#'">
                        <PrimaryButton class="bg-indigo-600 hover:bg-indigo-700">Apskatīt pieteikumus</PrimaryButton>
                    </Link>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
            <h4 class="text-md font-bold text-gray-800 mb-4">Pēdējās aktivitātes</h4>
            <p class="text-sm text-gray-500">Pagaidām šeit ir kluss. Izveidojiet pakalpojumus un piesakieties darbiem, lai sāktu!</p>
        </div>
    </div>
</template>