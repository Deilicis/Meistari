<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import { MagnifyingGlassIcon, Squares2X2Icon, ChevronDownIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import * as HeroIcons from '@heroicons/vue/24/outline';
import type { Category } from '@/types/models';

type ChildCategoryWithCount = Omit<Category, 'children'> & { job_requests_count: number };
type CategoryWithCount = Omit<Category, 'children'> & {
    job_requests_count: number;
    children?: ChildCategoryWithCount[];
};

const props = defineProps<{
    categories: CategoryWithCount[];
}>();

const PAGE_SIZE = 9;

const search = ref('');
const openId = ref<number | null>(null);
const page = ref(1);

watch(search, () => {
    page.value = 1;
    openId.value = null;
});

function toggle(category: CategoryWithCount) {
    if (!category.children?.length) return;
    openId.value = openId.value === category.id ? null : category.id;
}

const filtered = computed(() => {
    if (!search.value.trim()) return props.categories;
    const q = search.value.toLowerCase();
    return props.categories.filter(c =>
        c.name.toLowerCase().includes(q) ||
        c.children?.some(ch => ch.name.toLowerCase().includes(q))
    );
});

const totalPages = computed(() =>
    search.value.trim() ? 1 : Math.max(1, Math.ceil(filtered.value.length / PAGE_SIZE))
);

const paginated = computed(() => {
    if (search.value.trim()) return filtered.value;
    const start = (page.value - 1) * PAGE_SIZE;
    return filtered.value.slice(start, start + PAGE_SIZE);
});

function prevPage() {
    if (page.value > 1) { page.value--; openId.value = null; }
}
function nextPage() {
    if (page.value < totalPages.value) { page.value++; openId.value = null; }
}

const totalJobRequests = computed(() =>
    props.categories.reduce((sum, c) => {
        const childSum = c.children?.reduce((s, ch) => s + ch.job_requests_count, 0) ?? 0;
        return sum + c.job_requests_count + childSum;
    }, 0)
);

function categoryTotal(c: CategoryWithCount): number {
    return c.job_requests_count + (c.children?.reduce((s, ch) => s + ch.job_requests_count, 0) ?? 0);
}

function iconComponent(icon: string | null) {
    if (!icon) return null;
    return (HeroIcons as Record<string, unknown>)[icon] ?? null;
}
</script>

<template>
    <Head :title="t('browse.job_listings_title')" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1 bg-gold" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <Squares2X2Icon class="w-6 h-6 text-gold" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ t('browse.job_listings_title') }}</h1>
                        <p class="text-white/50 text-sm mt-0.5">
                            {{ t('browse.categories_subtitle') }} ·
                            <span class="text-gold font-semibold">{{ t('browse.job_listings_active_count', { n: totalJobRequests }) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6 pb-40">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="relative mb-6 max-w-md">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('browse.categories_search')"
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy/40 transition"
                    />
                </div>

                <EmptyState
                    v-if="filtered.length === 0"
                    :title="t('browse.categories_empty_title')"
                    :description="t('browse.categories_empty_desc')"
                >
                    <template #icon>
                        <MagnifyingGlassIcon class="w-8 h-8 text-navy/30" />
                    </template>
                </EmptyState>

                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">


                    <Link
                        v-if="page === 1 || !!search"
                        :href="route('master.job-requests.index')"
                        class="group flex flex-col items-center justify-center gap-3 rounded-2xl bg-navy border border-navy p-6 text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                    >
                        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <Squares2X2Icon class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm leading-snug">{{ t('browse.all_listings') }}</p>
                            <p class="text-xs text-white/60 mt-0.5">{{ t('browse.listings_count', { n: totalJobRequests }) }}</p>
                        </div>
                    </Link>

                    <div
                        v-for="category in paginated"
                        :key="category.id"
                        class="relative"
                    >
                        <button
                            type="button"
                            @click="toggle(category)"
                            class="w-full group flex flex-col items-center justify-center gap-3 rounded-2xl bg-white border p-6 text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                            :class="openId === category.id ? 'border-navy/30' : 'border-gray-100'"
                        >
                            <div class="w-14 h-14 rounded-2xl bg-navy/5 flex items-center justify-center group-hover:bg-navy/10 transition-colors">
                                <component
                                    :is="iconComponent(category.icon)"
                                    v-if="iconComponent(category.icon)"
                                    class="w-7 h-7 text-navy/60"
                                />
                                <svg v-else class="w-7 h-7 text-navy/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-navy text-sm leading-snug">{{ category.name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ t('browse.listings_count', { n: categoryTotal(category) }) }}</p>
                            </div>
                            <ChevronDownIcon
                                v-if="category.children?.length"
                                class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                :class="openId === category.id ? 'rotate-180' : ''"
                            />
                        </button>

                        
                        <div
                            v-if="openId === category.id"
                            class="absolute z-20 left-0 right-0 mt-1 bg-white border border-navy/20 rounded-xl shadow-lg overflow-hidden"
                        >
                            <Link
                                :href="route('master.job-requests.index', { category_id: category.id })"
                                class="flex items-center justify-between px-4 py-2.5 text-sm font-medium text-navy hover:bg-navy/5 transition-colors border-b border-gray-100"
                            >
                                {{ t('browse.all_label_prefix') }} {{ category.name }}
                            </Link>
                            <Link
                                v-for="child in category.children"
                                :key="child.id"
                                :href="route('master.job-requests.index', { category_id: child.id })"
                                class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-navy/5 hover:text-navy transition-colors"
                            >
                                <span>{{ child.name }}</span>
                                <span class="text-xs text-gray-400">{{ child.job_requests_count }}</span>
                            </Link>
                        </div>
                    </div>

                </div>

                <div
                    v-if="totalPages > 1 && !search"
                    class="flex items-center justify-center gap-2 mt-8"
                >
                    <button
                        @click="prevPage"
                        :disabled="page === 1"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-navy transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        :aria-label="t('common.prev_page')"
                    >
                        <ChevronLeftIcon class="w-4 h-4" />
                    </button>

                    <button
                        v-for="p in totalPages"
                        :key="p"
                        @click="page = p; openId = null"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg border text-sm font-medium transition-colors"
                        :class="p === page
                            ? 'bg-navy text-white border-navy'
                            : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-navy'"
                    >
                        {{ p }}
                    </button>

                    <button
                        @click="nextPage"
                        :disabled="page === totalPages"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-navy transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        :aria-label="t('common.next_page')"
                    >
                        <ChevronRightIcon class="w-4 h-4" />
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
