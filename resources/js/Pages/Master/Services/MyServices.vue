<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import ServiceModal from '@/Components/Services/ServiceModal.vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import ServiceCard from '@/Components/Services/ServiceCard.vue';
import MyServicesSearchBar from '@/Components/Search/MyServicesSearchBar.vue';

import type { Service, Category } from '@/types/models';

const props = defineProps<{
    services: { data: Service[] };
    categories: Category[];
    filters?: { search?: string; category_id?: string; is_active?: string; price_min?: string; price_max?: string };
}>();

const filterForm = ref({
    search: props.filters?.search ?? '',
    category_id: props.filters?.category_id ?? '',
    is_active: props.filters?.is_active ?? '',
    price_min: props.filters?.price_min ?? '',
    price_max: props.filters?.price_max ?? '',
});

let searchTimeout: ReturnType<typeof setTimeout>;

watch(filterForm, (newFilters) => {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        router.get(route('master.services.index'), newFilters, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300);
}, { deep: true });

const isServiceModalOpen = ref(false);
const serviceToEdit = ref<Service | null>(null);
const isDeleteModalOpen = ref(false);
const serviceToDelete = ref<number | null>(null);

const refreshServices = () => {
    router.reload({ only: ['services'] });
};

const openCreateModal = () => {
    serviceToEdit.value = null;
    isServiceModalOpen.value = true;
};

const openEditModal = (service: Service) => {
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
    } catch {
        toast.error('Neizdevās izdzēst pakalpojumu.');
    } finally {
        isDeleteModalOpen.value = false;
        serviceToDelete.value = null;
    }
};

const clearFilters = () => {
    filterForm.value = {
        search: '',
        category_id: '',
        is_active: '',
        price_min: '',
        price_max: '',
    };
};
</script>

<template>
    <Head title="Mani Pakalpojumi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-navy">Mani Pakalpojumi</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ props.services.data.length }} pakalpojum{{ props.services.data.length === 1 ? 's' : 'i' }}</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Jauns pakalpojums
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <MyServicesSearchBar 
                    v-model="filterForm" 
                    :categories="categories" 
                />

                <div v-if="!props.services.data || props.services.data.length === 0"
                    class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-navy/5 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-navy/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-1">Nav atrasts neviens pakalpojums</h3>
                    <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">
                        {{ Object.values(filterForm).some(x => x !== '') ? 'Mēģiniet mainīt meklēšanas kritērijus.' : 'Publicējiet savu pirmo pakalpojumu un piesaistiet klientus.' }}
                    </p>
                    <PrimaryButton v-if="Object.values(filterForm).some(x => x !== '')" @click="clearFilters">
                        Notīrīt filtrus
                    </PrimaryButton>
                    <PrimaryButton v-else @click="openCreateModal">
                        Izveidot pirmo pakalpojumu
                    </PrimaryButton>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ServiceCard 
                        v-for="service in props.services.data" 
                        :key="service.id" 
                        :service="service"
                        @edit="openEditModal"
                        @delete="confirmDelete"
                    />
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