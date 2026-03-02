<script setup lang="ts">
import SecondaryButton from '@/Components/Form/SecondaryButton.vue';
import DangerButton from '@/Components/Form/DangerButton.vue';

defineProps<{
    service: any;
}>();

const emit = defineEmits(['edit', 'delete']);
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-md transition">
        <div class="flex justify-between items-start mb-4">
            <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                :class="service.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            >
                {{ service.is_active ? 'Aktīvs' : 'Neaktīvs' }}
            </span>
            <span class="text-lg font-bold text-gray-900">
                {{ service.price ? service.price + ' €' : 'Vienojoties' }}
            </span>
        </div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-2 truncate">{{ service.title }}</h3>
        <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-grow">{{ service.description }}</p>
        
        <div class="flex flex-col items-left gap-2 text-xs text-gray-500 mb-6">
            <span>{{ service.category?.name || 'Nav kategorijas' }}</span>
            <span v-if="service.location && service.location.length > 0">
                {{ service.location.join(', ') }}
            </span>
        </div>
        
        <div class="flex gap-2 mt-auto border-t pt-4">
            <SecondaryButton @click="emit('edit', service)" class="flex-1 justify-center">
                Rediģēt
            </SecondaryButton>
            <DangerButton @click="emit('delete', service.id)" class="px-3">
                Dzēst
            </DangerButton>
        </div>
    </div>
</template>