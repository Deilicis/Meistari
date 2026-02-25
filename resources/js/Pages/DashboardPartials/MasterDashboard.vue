<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-600">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Sveiks, Meistar!</h3>
            <p class="text-sm text-gray-500 mb-4">Pārvaldi savu profilu, pakalpojumus un apskati jaunākos darba pieprasījumus savā apkārtnē.</p>
            <div class="flex gap-4">
                <PrimaryButton class="bg-blue-600 hover:bg-blue-700">
                    Meklēt darbus
                </PrimaryButton>
                <button class="px-4 py-2 border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Rediģēt profilu
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                <h4 class="font-semibold text-gray-700 mb-2">Mani pieteikumi</h4>
                <p class="text-sm text-gray-500">Nav aktīvu pieteikumu.</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                <h4 class="font-semibold text-gray-700 mb-2">Jauni ziņojumi</h4>
                <p class="text-sm text-gray-500">Tev nav jaunu ziņojumu.</p>
            </div>
        </div>
    </div>
</template>