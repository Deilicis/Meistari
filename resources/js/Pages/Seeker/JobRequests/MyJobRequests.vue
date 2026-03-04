<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import JobRequestModal from '@/Components/JobRequests/JobRequestModal.vue';

const props = defineProps<{
    jobRequests: any[];
    categories: any[];
}>();

const isJobModalOpen = ref(false);
const jobToEdit = ref<any | null>(null);
const isDeleteModalOpen = ref(false);
const jobToDelete = ref<number | null>(null);

const refreshJobs = () => {
    toast.success('Sludinājums veiksmīgi saglabāts!');
    router.reload({ only: ['jobRequests'] });
};

const openCreateModal = () => {
    jobToEdit.value = null;
    isJobModalOpen.value = true;
};

const openEditModal = (job: any) => {
    jobToEdit.value = job;
    isJobModalOpen.value = true;
};

const confirmDelete = (id: number) => {
    jobToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const executeDelete = async () => {
    if (!jobToDelete.value) return;

    try {
        await axios.delete(`/api/job-requests/${jobToDelete.value}`);
        toast.success('Sludinājums veiksmīgi izdzēsts!');
        router.reload({ only: ['jobRequests'] });
    } catch (error) {
        toast.error('Neizdevās izdzēst sludinājumu.');
    } finally {
        isDeleteModalOpen.value = false;
        jobToDelete.value = null;
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('lv-LV', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head title="Mani Sludinājumi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Mani Darba Sludinājumi
                </h2>
                <PrimaryButton @click="openCreateModal" class="bg-navy hover:bg-navy-hover">
                    + Jauns sludinājums
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div v-if="!jobRequests || jobRequests.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center border-2 border-dashed border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="text-gray-500 mb-4">Jums vēl nav neviena darba sludinājuma.</p>
                    <PrimaryButton @click="openCreateModal">
                        Izveidot pirmo sludinājumu
                    </PrimaryButton>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="job in jobRequests" :key="job.id" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col transition-all hover:shadow-md">
                        <div class="h-48 w-full bg-gray-100 relative">
                            <img v-if="job.images && job.images.length > 0" :src="`/storage/${job.images[0]}`" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="h-12 w-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            
                            <div class="absolute top-3 right-3">
                                <span v-if="job.status === 'open'" class="px-2.5 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">Aktīvs</span>
                                <span v-else class="px-2.5 py-1 bg-gray-100 text-gray-800 text-xs font-bold rounded-full">Slēgts</span>
                            </div>
                        </div>

                        <div class="p-5 flex-grow flex flex-col">
                            <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-1">{{ job.title }}</h3>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-3 space-x-4">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    {{ job.location && job.location.length ? job.location[0] : 'Nav norādīts' }}
                                </span>
                                <span v-if="job.budget" class="flex items-center font-semibold text-gray-700">
                                    {{ job.budget }} €
                                </span>
                            </div>

                            <p class="text-gray-600 text-sm line-clamp-3 mb-4">{{ job.description }}</p>

                            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    {{ job.applications_count || 0 }} pieteikumi
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="openEditModal(job)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors" title="Rediģēt">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="confirmDelete(job.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Dzēst">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <JobRequestModal 
            :show="isJobModalOpen" 
            :jobRequest="jobToEdit" 
            :categories="categories" 
            @close="isJobModalOpen = false" 
            @saved="refreshJobs" 
        />

        <ConfirmationModal
            :show="isDeleteModalOpen"
            title="Dzēst sludinājumu"
            message="Vai tiešām vēlaties neatgriezeniski dzēst šo sludinājumu? Visi saistītie pieteikumi un faili tiks dzēsti."
            confirmText="Jā, dzēst"
            confirmButtonClass="bg-red-600 hover:bg-red-700"
            @close="isDeleteModalOpen = false"
            @confirm="executeDelete"
        />

    </AuthenticatedLayout>
</template>