<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BrowseJobRequestsSearchBar from '@/Components/Search/BrowseJobRequestsSearchBar.vue';
import PublicJobRequestCard from '@/Components/JobRequests/PublicJobRequestCard.vue';
import JobRequestDetailModal from '@/Components/JobRequests/JobRequestDetailModal.vue';
import JobApplyModal from '@/Components/JobRequests/JobApplyModal.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import { useDebouncedFilter } from '@/composables/useDebouncedFilter';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import type { JobRequestWithSeeker, Category } from '@/types/models';

const props = defineProps<{
    jobRequests: {
        data: JobRequestWithSeeker[];
        current_page: number;
        last_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    categories: Category[];
    filters: {
        search?: string;
        category_id?: string;
        budget_min?: string;
        budget_max?: string;
    };
    appliedJobIds: number[];
}>();

const { filterForm, clearFilters, hasActiveFilters } = useDebouncedFilter(
    'master.job-requests.index',
    {
        search:      props.filters?.search      ?? '',
        category_id: props.filters?.category_id ?? '',
        budget_min:  props.filters?.budget_min  ?? '',
        budget_max:  props.filters?.budget_max  ?? '',
    },
    { search: '', category_id: '', budget_min: '', budget_max: '' }
);

const selectedJob = ref<JobRequestWithSeeker | null>(null);
const applyTarget = ref<JobRequestWithSeeker | null>(null);
const localApplied = ref<number[]>([...props.appliedJobIds]);

const isApplied = (jobId: number) => localApplied.value.includes(jobId);

const openDetail = (job: JobRequestWithSeeker) => { selectedJob.value = job; };
const closeDetail = () => { selectedJob.value = null; };
const openApply = () => { applyTarget.value = selectedJob.value; };
const closeApply = () => { applyTarget.value = null; };

const onApplied = () => {
    if (applyTarget.value) localApplied.value.push(applyTarget.value.id);
    closeApply();
    closeDetail();
};
</script>

<template>
    <Head title="Darba sludinājumi" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1 bg-gold" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <MagnifyingGlassIcon class="w-6 h-6 text-gold" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">Darba sludinājumi</h1>
                        <p class="text-white/50 text-sm mt-0.5">
                            <span class="text-gold font-semibold">{{ jobRequests.total }}</span> aktīvi sludinājumi
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <BrowseJobRequestsSearchBar v-model="filterForm" :categories="categories" />

                <EmptyState
                    v-if="jobRequests.data.length === 0"
                    :title="hasActiveFilters() ? 'Nav atrasts neviens sludinājums' : 'Pagaidām nav aktīvu sludinājumu'"
                    :description="hasActiveFilters() ? 'Mēģiniet mainīt meklēšanas kritērijus.' : 'Meklētāji vēl nav publicējuši darba sludinājumus.'"
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

                <div v-else class="flex flex-col gap-3">
                    <PublicJobRequestCard
                        v-for="job in jobRequests.data"
                        :key="job.id"
                        :job="job"
                        :applied="isApplied(job.id)"
                        @open="openDetail"
                    />
                </div>

                <div v-if="jobRequests.last_page > 1" class="mt-8 flex justify-center gap-1">
                    <template v-for="link in jobRequests.links" :key="link.label">
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

        <JobRequestDetailModal
            :show="!!selectedJob"
            :job="selectedJob"
            :applied="selectedJob ? isApplied(selectedJob.id) : false"
            @close="closeDetail"
            @apply="openApply"
        />

        <JobApplyModal
            :show="!!applyTarget"
            :job="applyTarget"
            @close="closeApply"
            @applied="onApplied"
        />

    </AuthenticatedLayout>
</template>
