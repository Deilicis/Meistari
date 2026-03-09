<script setup lang="ts">
import TextInput from '@/Components/Form/TextInput.vue';
import type { Category } from '@/types/models';

defineProps<{
    categories: Category[];
}>();

const filterForm = defineModel<{
    search: string;
    category_id: string | number;
    is_active: string | number;
    price_min: string | number;
    price_max: string | number;
}>({ required: true });

</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <div class="md:col-span-2">
                <TextInput 
                    v-model="filterForm.search" 
                    type="text" 
                    class="w-full" 
                    placeholder="Meklēt pēc nosaukuma..." 
                />
            </div>
            
            <div>
                <select v-model="filterForm.category_id" class="border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm w-full">
                    <option value="">Visas kategorijas</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>
            
            <div>
                <select v-model="filterForm.is_active" class="border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm w-full">
                    <option value="">Visi statusi</option>
                    <option value="1">Tikai aktīvie</option>
                    <option value="0">Tikai neaktīvie</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <TextInput v-model="filterForm.price_min" type="number" class="w-full" placeholder="No €" />
                <span class="text-gray-400">-</span>
                <TextInput v-model="filterForm.price_max" type="number" class="w-full" placeholder="Līdz €" />
            </div>

        </div>
    </div>
</template>