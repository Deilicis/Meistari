<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import JobRequestModal from '@/Components/JobRequests/JobRequestModal.vue';
import MyJobRequestsSearchBar from '@/Components/Search/MyJobRequestsSearchBar.vue';
import { PlusIcon, MapPinIcon, CalendarIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import type { JobRequest, Category, JobStatus } from '@/types/models';

const props = defineProps<{
    jobRequests: JobRequest[];
    categories: Category[];
    filters?: {
        search?: string;
        category_id?: string;
        status?: string;
        budget_min?: string;
        budget_max?: string;
    };
}>();

const filterForm = ref({
    search: props.filters?.search ?? '',
    category_id: props.filters?.category_id ?? '',
    status: props.filters?.status ?? '',
    budget_min: props.filters?.budget_min ?? '',
    budget_max: props.filters?.budget_max ?? '',
});

let searchTimeout: ReturnType<typeof setTimeout>;

watch(filterForm, (newFilters) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('seeker.job-requests.index'), newFilters, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
}, { deep: true });

const clearFilters = () => {
    filterForm.value = { search: '', category_id: '', status: '', budget_min: '', budget_max: '' };
};

const hasActiveFilters = () => Object.values(filterForm.value).some(x => x !== '');

const statusConfig: Record<JobStatus, { label: string; classes: string }> = {
    active:    { label: 'Aktīvs',    classes: 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200' },
    assigned:  { label: 'Piešķirts', classes: 'bg-blue-100 text-blue-800 ring-1 ring-blue-200' },
    completed: { label: 'Pabeigts',  classes: 'bg-navy-light text-white' },
    cancelled: { label: 'Atcelts',   classes: 'bg-red-100 text-red-800 ring-1 ring-red-200' },
};

const isJobModalOpen = ref(false);
const jobToEdit = ref<JobRequest | null>(null);
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

const openEditModal = (job: JobRequest) => {
    jobToEdit.value = job;
    isJobModalOpen.value = true;
};

const confirmDelete = (id: number) => {
    jobToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!jobToDelete.value) return;

    router.delete(route('api.job-requests.destroy', jobToDelete.value), {
        preserveScroll: true,
        onSuccess: () => toast.success('Sludinājums veiksmīgi izdzēsts!'),
        onError: () => toast.error('Neizdevās izdzēst sludinājumu.'),
        onFinish: () => {
            isDeleteModalOpen.value = false;
            jobToDelete.value = null;
        },
    });
};

const formatDate = (dateString: string | null): string => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('lv-LV', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatBudget = (budget: number | null): string => {
    if (!budget) return '';
    return new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(budget);
};
</script>

<template>
    <Head title="Mani Sludinājumi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-navy">Mani Darba Sludinājumi</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ jobRequests.length }} sludinājum{{ jobRequests.length === 1 ? 's' : 'i' }}</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-navy-hover transition-colors"
                >
                    <PlusIcon class="w-4 h-4" />
                    Jauns sludinājums
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <MyJobRequestsSearchBar
                    v-model="filterForm"
                    :categories="categories"
                />

                <!-- Tukšs -->
                <div v-if="!jobRequests || jobRequests.length === 0"
                    class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-navy/5 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-navy/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-1">
                        {{ hasActiveFilters() ? 'Nav atrasts neviens sludinājums' : 'Nav neviena sludinājuma' }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">
                        {{ hasActiveFilters()
                            ? 'Mēģiniet mainīt meklēšanas kritērijus.'
                            : 'Publicējiet savu pirmo darba sludinājumu un saņemiet piedāvājumus no meistariem.' }}
                    </p>
                    <button
                        v-if="hasActiveFilters()"
                        @click="clearFilters"
                        class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-navy-hover transition-colors"
                    >
                        Notīrīt filtrus
                    </button>
                    <button
                        v-else
                        @click="openCreateModal"
                        class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-navy-hover transition-colors"
                    >
                        Izveidot pirmo sludinājumu
                    </button>
                </div>

                <!-- Sludinājumi -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="job in jobRequests"
                        :key="job.id"
                        class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col transition-shadow hover:shadow-md"
                    >
                        <div class="relative bg-navy px-5 pt-5 pb-8">
                            <span
                                class="absolute top-3 right-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                :class="statusConfig[job.status]?.classes ?? statusConfig.active.classes"
                            >
                                {{ statusConfig[job.status]?.label ?? job.status }}
                            </span>
                            <span v-if="job.category" class="inline-flex items-center gap-1 text-xs font-semibold text-gold bg-navy-light rounded px-2 py-0.5 mb-2 tracking-wide uppercase">
                                {{ job.category.name }}
                            </span>

                            <h3 class="text-base font-bold text-white line-clamp-2 pr-16">
                                {{ job.title }}
                            </h3>
                        </div>

                        <div v-if="job.images && job.images.length > 0" class="-mt-4 mx-4 rounded-xl overflow-hidden shadow-sm h-32 flex-shrink-0">
                            <img
                                :src="`/storage/${job.images[0]}`"
                                :alt="job.title"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <div class="p-5 flex-grow flex flex-col" :class="{ 'pt-3': job.images && job.images.length > 0 }">
                            <p class="text-sm text-gray-600 line-clamp-2 mb-4">{{ job.description }}</p>
                            <div class="space-y-1.5 mb-4">
                                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <MapPinIcon class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" />
                                    <span>{{ job.location?.length ? job.location.join(', ') : 'Nav norādīts' }}</span>
                                </div>

                                <div v-if="job.deadline" class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <CalendarIcon class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" />
                                    <span>Termiņš: {{ formatDate(job.deadline) }}</span>
                                </div>
                            </div>

                            <!-- Footeris -->
                            <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span v-if="job.budget" class="text-sm font-bold text-navy">
                                        {{ formatBudget(job.budget) }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ job.applications_count ?? 0 }} pieteikum{{ (job.applications_count ?? 0) === 1 ? 's' : 'i' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <button
                                        @click="openEditModal(job)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-navy hover:bg-navy/5 transition-colors"
                                        title="Rediģēt"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="confirmDelete(job.id)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Dzēst"
                                    >
                                        <TrashIcon class="w-4 h-4" />
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

        <ConfirmDialog
            :show="isDeleteModalOpen"
            title="Dzēst sludinājumu?"
            message="Vai tiešām vēlaties neatgriezeniski dzēst šo sludinājumu? Visi saistītie pieteikumi un faili tiks dzēsti."
            confirmLabel="Jā, dzēst"
            @confirm="executeDelete"
            @cancel="isDeleteModalOpen = false"
        />

    </AuthenticatedLayout>
</template>
