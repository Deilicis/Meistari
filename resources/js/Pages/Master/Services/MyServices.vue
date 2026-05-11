<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const { t } = useI18n();
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import ServiceModal from '@/Components/Services/ServiceModal.vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import ServiceCard from '@/Components/Services/ServiceCard.vue';
import MyServicesSearchBar from '@/Components/Search/MyServicesSearchBar.vue';

import { BriefcaseIcon, PlusIcon, InboxArrowDownIcon } from '@heroicons/vue/24/outline';
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
        toast.success(t('services.deleted_toast'));
        refreshServices();
    } catch {
        toast.error(t('services.delete_failed'));
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
    <Head :title="t('services.my_title')" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1 bg-gold" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <BriefcaseIcon class="w-6 h-6 text-gold" />
                        <div>
                            <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ t('services.my_title') }}</h1>
                            <p class="text-white/50 text-sm mt-0.5">
                                <span class="text-gold font-semibold">{{ t('services.my_count', props.services.data.length) }}</span>
                            </p>
                        </div>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 bg-gold text-navy text-sm font-bold px-4 py-2 rounded-xl hover:bg-yellow-400 transition-colors shrink-0"
                    >
                        <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                        {{ t('services.add_new_btn') }}
                    </button>
                </div>
            </div>
        </div>

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
                    <h3 class="text-base font-semibold text-gray-900 mb-1">{{ t('services.empty_filter_title') }}</h3>
                    <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">
                        {{ Object.values(filterForm).some(x => x !== '') ? t('services.empty_filter_desc') : t('services.empty_first_desc') }}
                    </p>
                    <PrimaryButton v-if="Object.values(filterForm).some(x => x !== '')" @click="clearFilters">
                        {{ t('services.clear_filters_btn') }}
                    </PrimaryButton>
                    <PrimaryButton v-else @click="openCreateModal">
                        {{ t('services.create_first_btn') }}
                    </PrimaryButton>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="service in props.services.data" :key="service.id" class="flex flex-col gap-0">
                        <ServiceCard
                            :service="service"
                            @edit="openEditModal"
                            @delete="confirmDelete"
                        />
                        <div class="flex items-center justify-between bg-navy/[0.03] border border-t-0 border-gray-100 rounded-b-xl px-4 py-2">
                            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                                <InboxArrowDownIcon class="w-3.5 h-3.5 text-gray-400" />
                                <span class="font-semibold text-navy">{{ t('services.applications_count', service.applications_count ?? 0) }}</span>
                            </span>
                            <a
                                :href="route('master.service-applications.for-service', service.id)"
                                class="text-xs font-semibold text-gold hover:text-yellow-500 transition-colors"
                            >
                                {{ t('services.view_applications_link') }}
                            </a>
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
            :title="t('services.delete_confirm_title')"
            :message="t('services.delete_confirm_message')"
            :confirmText="t('services.delete_confirm_btn')"
            confirmButtonClass="bg-red-600 hover:bg-red-700"
            @close="isDeleteModalOpen = false"
            @confirm="executeDelete"
        />

    </AuthenticatedLayout>
</template>