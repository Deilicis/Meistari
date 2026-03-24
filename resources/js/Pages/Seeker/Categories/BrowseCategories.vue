<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryCard from '@/Components/Category/CategoryCard.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import { MagnifyingGlassIcon, Squares2X2Icon } from '@heroicons/vue/24/outline';
import type { Category } from '@/types/models';

const props = defineProps<{
    categories: (Category & { services_count: number })[];
}>();

const search = ref('');

const filtered = computed(() => {
    if (!search.value.trim()) return props.categories;
    const q = search.value.toLowerCase();
    return props.categories.filter(c => c.name.toLowerCase().includes(q));
});

const totalServices = computed(() =>
    props.categories.reduce((sum, c) => sum + c.services_count, 0)
);
</script>

<template>
    <Head title="Kategorijas" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-navy">Kategorijas</h2>
                <p class="text-sm text-gray-500 mt-0.5">Izvēlies kategoriju, lai atrastu piemērotus pakalpojumus</p>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="relative mb-6 max-w-md">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Meklēt kategoriju..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy/40 transition"
                    />
                </div>

                <EmptyState
                    v-if="filtered.length === 0"
                    title="Nav atrasta neviena kategorija"
                    description="Mēģiniet mainīt meklēšanas vārdu."
                >
                    <template #icon>
                        <MagnifyingGlassIcon class="w-8 h-8 text-navy/30" />
                    </template>
                </EmptyState>

                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

                    <Link
                        :href="route('seeker.services.index')"
                        class="group flex flex-col items-center justify-center gap-3 rounded-2xl bg-navy border border-navy p-6 text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                    >
                        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <Squares2X2Icon class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm leading-snug">Visi pakalpojumi</p>
                            <p class="text-xs text-white/60 mt-0.5">{{ totalServices }} pakalpojumi</p>
                        </div>
                    </Link>

                    <CategoryCard
                        v-for="category in filtered"
                        :key="category.id"
                        :category="category"
                    />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
