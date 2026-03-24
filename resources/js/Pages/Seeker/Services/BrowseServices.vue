<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BrowseServicesSearchBar from '@/Components/Search/BrowseServicesSearchBar.vue';
import PublicServiceCard from '@/Components/Services/PublicServiceCard.vue';
import ServiceDetailModal from '@/Components/Services/ServiceDetailModal.vue';
import ServiceApplyModal from '@/Components/Services/ServiceApplyModal.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import { useDebouncedFilter } from '@/composables/useDebouncedFilter';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import type { ServiceWithMaster, Category } from '@/types/models';

const props = defineProps<{
    services: {
        data: ServiceWithMaster[];
        current_page: number;
        last_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    categories: Category[];
    filters: {
        search?: string;
        category_id?: string;
        price_min?: string;
        price_max?: string;
    };
    appliedServiceIds: number[];
}>();

const { filterForm, clearFilters, hasActiveFilters } = useDebouncedFilter(
    'seeker.services.index',
    {
        search: props.filters?.search      ?? '',
        category_id: props.filters?.category_id ?? '',
        price_min: props.filters?.price_min   ?? '',
        price_max: props.filters?.price_max   ?? '',
    },
    { search: '', category_id: '', price_min: '', price_max: '' }
);

const selectedService = ref<ServiceWithMaster | null>(null);
const applyTarget = ref<ServiceWithMaster | null>(null);
const localApplied = ref<number[]>([...props.appliedServiceIds]);

const isApplied = (serviceId: number) => localApplied.value.includes(serviceId);

const openDetail = (service: ServiceWithMaster) => { selectedService.value = service; };
const closeDetail = () => { selectedService.value = null; };
const openApply = () => { applyTarget.value = selectedService.value; };
const closeApply = () => { applyTarget.value = null; };

const onApplied = () => {
    if (applyTarget.value) localApplied.value.push(applyTarget.value.id);
    closeApply();
    closeDetail();
};
</script>

<template>
    <Head title="Pakalpojumi" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-navy">Pakalpojumi</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ services.total }} pieejami pakalpojumi</p>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <BrowseServicesSearchBar v-model="filterForm" :categories="categories" />

                <EmptyState
                    v-if="services.data.length === 0"
                    :title="hasActiveFilters() ? 'Nav atrasts neviens pakalpojums' : 'Pagaidām nav pieejamu pakalpojumu'"
                    :description="hasActiveFilters() ? 'Mēģiniet mainīt meklēšanas kritērijus.' : 'Meistari vēl nav publicējuši savus pakalpojumus.'"
                >
                    <template #icon>
                        <MagnifyingGlassIcon class="w-8 h-8 text-navy/30" />
                    </template>
                    <template #action>
                        <button
                            v-if="hasActiveFilters()"
                            @click="clearFilters"
                            class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-navy-hover transition-colors"
                        >
                            Notīrīt filtrus
                        </button>
                    </template>
                </EmptyState>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <PublicServiceCard
                        v-for="service in services.data"
                        :key="service.id"
                        :service="service"
                        :applied="isApplied(service.id)"
                        @open="openDetail"
                    />
                </div>

                <div v-if="services.last_page > 1" class="mt-8 flex justify-center gap-1">
                    <template v-for="link in services.links" :key="link.label">
                        <button
                            v-if="link.url"
                            @click="router.get(link.url)"
                            class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                            :class="link.active
                                ? 'bg-navy text-white font-semibold'
                                : 'text-gray-600 hover:bg-gray-100'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-1.5 text-sm text-gray-300"
                            v-html="link.label"
                        />
                    </template>
                </div>

            </div>
        </div>

        <ServiceDetailModal
            :show="!!selectedService"
            :service="selectedService"
            :applied="selectedService ? isApplied(selectedService.id) : false"
            @close="closeDetail"
            @apply="openApply"
        />

        <ServiceApplyModal
            :show="!!applyTarget"
            :service="applyTarget"
            @close="closeApply"
            @applied="onApplied"
        />

    </AuthenticatedLayout>
</template>
