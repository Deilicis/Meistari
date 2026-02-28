<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ServiceModal from '@/Pages/Master/Services/ServiceModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps<{
    services: { data: any[] };
    categories: any[];
}>();

const isServiceModalOpen = ref(false);
const serviceToEdit = ref<any | null>(null);
const isDeleteModalOpen = ref(false);
const serviceToDelete = ref<number | null>(null);

const refreshServices = () => {
    router.reload({ only: ['services'] });
};

const openCreateModal = () => {
    serviceToEdit.value = null;
    isServiceModalOpen.value = true;
};

const openEditModal = (service: any) => {
    serviceToEdit.value = service;
    isServiceModalOpen.value = true;
};

const confirmDelete = (id: number) => {
    serviceToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const executeDelete = async () => {
    if (!serviceToDelete.value) return;

    try {
        await axios.delete(`/api/services/${serviceToDelete.value}`);
        toast.success('Pakalpojums veiksmīgi izdzēsts!');
        refreshServices();
    } catch (error) {
        toast.error('Neizdevās izdzēst pakalpojumu.');
    } finally {
        isDeleteModalOpen.value = false;
        serviceToDelete.value = null;
    }
};
</script>

<template>
    <Head title="Mani Pakalpojumi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Mani Pakalpojumi
                </h2>
                <PrimaryButton @click="openCreateModal">
                    Pievienot Pakalpojumu
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div v-if="!props.services.data || props.services.data.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center border-2 border-dashed border-gray-200">
                    <p class="text-gray-500 mb-4">Tev vēl nav pievienots neviens pakalpojums.</p>
                    <PrimaryButton @click="openCreateModal">Izveidot pirmo pakalpojumu</PrimaryButton>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="service in props.services.data" :key="service.id" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="service.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                {{ service.is_active ? 'Aktīvs' : 'Neaktīvs' }}
                            </span>
                            <span class="text-lg font-bold text-gray-900">{{ service.price ? service.price + ' €' : 'Vienojoties' }}</span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 mb-2 truncate">{{ service.title }}</h3>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-grow">{{ service.description }}</p>
                        
                        <div class="flex items-center gap-2 text-xs text-gray-500 mb-6">
                            <span>📂 {{ service.category?.name || 'Nav kategorijas' }}</span>
                            <span>📍 {{ service.location }}</span>
                        </div>
                        
                        <div class="flex gap-2 mt-auto border-t pt-4">
                            <SecondaryButton @click="openEditModal(service)" class="flex-1 justify-center">
                                Rediģēt
                            </SecondaryButton>
                            <DangerButton @click="confirmDelete(service.id)" class="px-3">
                                Dzēst
                            </DangerButton>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <ServiceModal 
            :show="isServiceModalOpen" 
            :service="serviceToEdit" 
            :categories="categories" 
            @close="isServiceModalOpen = false" 
            @saved="refreshServices" 
        />

        <ConfirmationModal
            :show="isDeleteModalOpen"
            title="Dzēst pakalpojumu"
            message="Vai tiešām vēlaties neatgriezeniski dzēst šo pakalpojumu? Šo darbību nevarēs atsaukt."
            confirmText="Jā, dzēst"
            confirmButtonClass="bg-red-600 hover:bg-red-700"
            @close="isDeleteModalOpen = false"
            @confirm="executeDelete"
        />

    </AuthenticatedLayout>
</template>