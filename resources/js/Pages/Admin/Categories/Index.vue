<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminCategoryCreateModal from '@/Components/Admin/Categories/AdminCategoryCreateModal.vue';
import AdminCategoryEditModal   from '@/Components/Admin/Categories/AdminCategoryEditModal.vue';
import AdminCategoryDeleteModal from '@/Components/Admin/Categories/AdminCategoryDeleteModal.vue';
import AdminCategoryMergeModal  from '@/Components/Admin/Categories/AdminCategoryMergeModal.vue';
import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowsRightLeftIcon,
    ChevronDownIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';
import type { AdminCategory } from '@/types/admin/category';

const props = defineProps<{
    categories: AdminCategory[];
}>();

// ─── State ───────────────────────────────────────────────────────────────────

const expanded       = ref<Set<number>>(new Set());
const loading        = ref<string | null>(null);

const showCreate  = ref(false);
const showEdit    = ref(false);
const showDelete  = ref(false);
const showMerge   = ref(false);

const createParent   = ref<AdminCategory | null>(null);
const editingCat     = ref<AdminCategory | null>(null);
const deletingCat    = ref<AdminCategory | null>(null);
const mergingSource  = ref<AdminCategory | null>(null);

// ─── Helpers ─────────────────────────────────────────────────────────────────

const allCategories = computed((): AdminCategory[] => {
    const result: AdminCategory[] = [];
    for (const cat of props.categories) {
        result.push(cat);
        for (const child of cat.children ?? []) {
            result.push(child);
        }
    }
    return result;
});

const totalCategories = computed(() => allCategories.value.length);

function toggle(id: number) {
    expanded.value.has(id) ? expanded.value.delete(id) : expanded.value.add(id);
}

function openCreate(parent?: AdminCategory) {
    createParent.value = parent ?? null;
    showCreate.value   = true;
}

function openEdit(cat: AdminCategory) {
    editingCat.value = cat;
    showEdit.value   = true;
}

function openDelete(cat: AdminCategory) {
    deletingCat.value = cat;
    showDelete.value  = true;
}

function openMerge(cat: AdminCategory) {
    mergingSource.value = cat;
    showMerge.value     = true;
}

function reload() {
    router.reload({ only: ['categories'] });
}

// ─── Actions ─────────────────────────────────────────────────────────────────

async function handleCreate(data: { name: string; parent_id: number | null; icon: string | null }) {
    loading.value = 'create';
    try {
        await axios.post(route('admin.categories.store'), data);
        toast.success('Kategorija pievienota.');
        showCreate.value = false;
        reload();
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? 'Radās kļūda.');
    } finally {
        loading.value = null;
    }
}

async function handleEdit(data: { name: string; icon: string | null; parent_id: number | null; regenerate_slug: boolean }) {
    if (!editingCat.value) return;
    loading.value = 'edit';
    try {
        await axios.put(route('admin.categories.update', editingCat.value.id), data);
        toast.success('Kategorija atjaunināta.');
        showEdit.value = false;
        reload();
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? 'Radās kļūda.');
    } finally {
        loading.value = null;
    }
}

async function handleDelete() {
    if (!deletingCat.value) return;
    loading.value = 'delete';
    try {
        await axios.delete(route('admin.categories.destroy', deletingCat.value.id));
        toast.success('Kategorija dzēsta.');
        showDelete.value = false;
        reload();
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? 'Radās kļūda.');
    } finally {
        loading.value = null;
    }
}

async function handleMerge(data: { source_id: number; target_id: number }) {
    loading.value = 'merge';
    try {
        await axios.post(route('admin.categories.merge'), data);
        toast.success('Kategorijas apvienotas.');
        showMerge.value = false;
        reload();
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? 'Radās kļūda.');
    } finally {
        loading.value = null;
    }
}
</script>

<template>
    <AdminLayout>
        <!-- Page header -->
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">Kategoriju pārvaldība</h1>
                        <p class="text-white/50 text-sm mt-0.5">
                            <span class="text-red-400 font-semibold">{{ totalCategories }}</span>
                            kategorija{{ totalCategories === 1 ? '' : 's' }} ({{ categories.length }} augstākā līmeņa)
                        </p>
                    </div>
                    <button
                        @click="openCreate()"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-navy bg-white hover:bg-gray-50 rounded-xl shadow-sm transition-colors"
                    >
                        <PlusIcon class="w-4 h-4" />
                        Pievienot kategoriju
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="categories.length === 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center text-gray-400 text-sm">
                Nav kategoriju.
            </div>

            <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div v-for="cat in categories" :key="cat.id">
                    <!-- Top-level row -->
                    <div
                        class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50/50 transition-colors"
                        :class="cat.is_system ? 'bg-slate-50/80' : ''"
                    >
                        <!-- Expand toggle -->
                        <button
                            v-if="cat.children && cat.children.length > 0"
                            @click="toggle(cat.id)"
                            class="text-gray-400 hover:text-navy transition-colors flex-shrink-0"
                        >
                            <ChevronDownIcon v-if="expanded.has(cat.id)" class="w-4 h-4" />
                            <ChevronRightIcon v-else class="w-4 h-4" />
                        </button>
                        <span v-else class="w-4 flex-shrink-0" />

                        <!-- Name + badges -->
                        <div class="flex-1 min-w-0 flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold text-gray-900">{{ cat.name }}</span>
                            <span v-if="cat.icon" class="text-xs text-gray-400">{{ cat.icon }}</span>
                            <span v-if="cat.is_system" class="text-[10px] font-bold px-1.5 py-0.5 bg-navy text-white rounded uppercase tracking-wide">Sistēmas</span>
                        </div>

                        <!-- Usage counts -->
                        <div class="text-xs flex-shrink-0 text-gray-400">
                            <span :class="cat.services_count > 0 ? 'text-amber-600 font-semibold' : ''">{{ cat.services_count }} pak.</span>
                            <span class="mx-1">·</span>
                            <span :class="cat.job_requests_count > 0 ? 'text-amber-600 font-semibold' : ''">{{ cat.job_requests_count }} darbi</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button
                                @click="openCreate(cat)"
                                title="Pievienot apakškategoriju"
                                class="p-1.5 text-gray-400 hover:text-navy hover:bg-navy/5 rounded-lg transition-colors"
                            >
                                <PlusIcon class="w-3.5 h-3.5" />
                            </button>
                            <button
                                @click="openEdit(cat)"
                                title="Rediģēt"
                                class="p-1.5 text-gray-400 hover:text-navy hover:bg-navy/5 rounded-lg transition-colors"
                            >
                                <PencilSquareIcon class="w-3.5 h-3.5" />
                            </button>
                            <button
                                v-if="!cat.is_system"
                                @click="openMerge(cat)"
                                title="Apvienot"
                                class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                            >
                                <ArrowsRightLeftIcon class="w-3.5 h-3.5" />
                            </button>
                            <button
                                v-if="!cat.is_system"
                                @click="openDelete(cat)"
                                :title="cat.can_delete ? 'Dzēst' : 'Nevar dzēst — ir lietojums vai apakškategorijas'"
                                class="p-1.5 rounded-lg transition-colors"
                                :class="cat.can_delete ? 'text-gray-400 hover:text-red-600 hover:bg-red-50' : 'text-gray-200 cursor-not-allowed'"
                            >
                                <TrashIcon class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- Subcategory rows -->
                    <template v-if="expanded.has(cat.id) && cat.children && cat.children.length > 0">
                        <div
                            v-for="child in cat.children"
                            :key="child.id"
                            class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 bg-slate-50/30 hover:bg-gray-50/60 transition-colors"
                        >
                            <span class="w-4 flex-shrink-0" />
                            <span class="w-3 flex-shrink-0 border-l-2 border-gray-200 h-full" />

                            <div class="flex-1 min-w-0 flex items-center gap-2 flex-wrap">
                                <span class="text-sm text-gray-700">{{ child.name }}</span>
                                <span v-if="child.icon" class="text-xs text-gray-400">{{ child.icon }}</span>
                            </div>

                            <div class="text-xs flex-shrink-0 text-gray-400">
                                <span :class="child.services_count > 0 ? 'text-amber-600 font-semibold' : ''">{{ child.services_count }} pak.</span>
                                <span class="mx-1">·</span>
                                <span :class="child.job_requests_count > 0 ? 'text-amber-600 font-semibold' : ''">{{ child.job_requests_count }} darbi</span>
                            </div>

                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button
                                    @click="openEdit(child)"
                                    title="Rediģēt"
                                    class="p-1.5 text-gray-400 hover:text-navy hover:bg-navy/5 rounded-lg transition-colors"
                                >
                                    <PencilSquareIcon class="w-3.5 h-3.5" />
                                </button>
                                <button
                                    @click="openMerge(child)"
                                    title="Apvienot"
                                    class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                >
                                    <ArrowsRightLeftIcon class="w-3.5 h-3.5" />
                                </button>
                                <button
                                    @click="openDelete(child)"
                                    :title="child.can_delete ? 'Dzēst' : 'Nevar dzēst — ir lietojums'"
                                    class="p-1.5 rounded-lg transition-colors"
                                    :class="child.can_delete ? 'text-gray-400 hover:text-red-600 hover:bg-red-50' : 'text-gray-200 cursor-not-allowed'"
                                >
                                    <TrashIcon class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <AdminCategoryCreateModal
            :show="showCreate"
            :parent-category="createParent"
            :top-level-categories="categories"
            @close="showCreate = false"
            @submit="handleCreate"
        />
        <AdminCategoryEditModal
            :show="showEdit"
            :category="editingCat"
            :top-level-categories="categories"
            @close="showEdit = false"
            @submit="handleEdit"
        />
        <AdminCategoryDeleteModal
            :show="showDelete"
            :category="deletingCat"
            @close="showDelete = false"
            @confirm="handleDelete"
        />
        <AdminCategoryMergeModal
            :show="showMerge"
            :source="mergingSource"
            :all-categories="categories"
            @close="showMerge = false"
            @submit="handleMerge"
        />
    </AdminLayout>
</template>
