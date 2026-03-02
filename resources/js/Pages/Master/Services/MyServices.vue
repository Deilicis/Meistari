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

const props = defineProps<{
    services: { data: any[] };
    categories: any[];
    filters?: any;
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
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Mani Pakalpojumi
                </h2>
                <PrimaryButton @click="openCreateModal">
                    Pievienot Pakalpojumu
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <MyServicesSearchBar 
                    v-model="filterForm" 
                    :categories="categories" 
                />

                <div v-if="!props.services.data || props.services.data.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center border-2 border-dashed border-gray-200">
                    <p class="text-gray-500 mb-4">Nav atrasts neviens pakalpojums, kas atbilstu kritērijiem.</p>
                    
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