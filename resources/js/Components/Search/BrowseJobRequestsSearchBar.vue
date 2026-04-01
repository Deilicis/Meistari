<script setup lang="ts">
import TextInput from '@/Components/Form/TextInput.vue';
import CategorySelect from '@/Components/Form/CategorySelect.vue';
import type { Category } from '@/types/models';

defineProps<{
    categories: Category[];
}>();

const filterForm = defineModel<{
    search: string;
    category_id: string | number;
    budget_min: string | number;
    budget_max: string | number;
}>({ required: true });
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-start">

            <div class="md:col-span-2">
                <TextInput
                    v-model="filterForm.search"
                    type="text"
                    class="w-full"
                    placeholder="Meklēt darba sludinājumus..."
                />
            </div>

            <div>
                <CategorySelect
                    v-model="filterForm.category_id"
                    :categories="categories"
                    :show-all-option="true"
                />
            </div>

            <div class="md:col-span-2 flex gap-2">
                <TextInput v-model="filterForm.budget_min" type="number" class="w-full" placeholder="Budžets no €" />
                <span class="text-gray-400 flex-shrink-0">–</span>
                <TextInput v-model="filterForm.budget_max" type="number" class="w-full" placeholder="Budžets līdz €" />
            </div>

        </div>
    </div>
</template>
