<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';

</script>

<template>
    <div class="space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex justify-between items-center border-l-4 border-navy">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Mani sludinājumi</h3>
                <p class="text-sm text-gray-500">Pārvaldi savus pieprasījumus un apskati meistaru piedāvājumus.</p>
            </div>
            <PrimaryButton class="bg-navy hover:bg-navy-hover">
                Pievienot jaunu sludinājumu
            </PrimaryButton>
        </div>

        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-10 text-center">
            <p class="text-gray-500">Tev pašlaik nav neviena aktīva sludinājuma.</p>
        </div>
    </div>
</template>