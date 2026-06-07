<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import Modal from '@/Components/Common/Modal.vue';
import {
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    CheckBadgeIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

interface Master {
    id: number;
    name: string;
    email: string;
    created_at: string;
    suspended_at: string | null;
    profile: {
        city: string | null;
        phone: string | null;
        is_verified: boolean;
        type: 'individual' | 'company' | null;
    } | null;
}

interface PaginatedMasters {
    data: Master[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

const props = defineProps<{
    masters: PaginatedMasters;
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const doSearch = () => {
    router.get(route('admin.masters.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, replace: true });
};
const hasFilters = () => !!props.filters.search || !!props.filters.status;

const editModal = ref(false);
const editTarget = ref<Master | null>(null);
const editForm = useForm({ name: '', email: '', is_verified: false });

const openEdit = (master: Master) => {
    editTarget.value = master;
    editForm.name = master.name;
    editForm.email = master.email;
    editForm.is_verified = master.profile?.is_verified ?? false;
    editModal.value = true;
};

const submitEdit = () => {
    if (!editTarget.value) return;
    editForm.put(route('admin.masters.update', editTarget.value.id), {
        onSuccess: () => { editModal.value = false; },
    });
};

const deleteTarget = ref<Master | null>(null);
const deleteProcessing = ref(false);

const doDelete = () => {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(route('admin.masters.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteTarget.value = null;
        },
    });
};

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });

const typeLabel = (type: string | null) => type === 'company' ? 'Uzņēmums' : 'Privātpersona';
const typeClass = (type: string | null) => type === 'company'
    ? 'bg-blue-100 text-blue-700'
    : 'bg-gray-100 text-gray-600';
</script>

<template>
    <Head title="Meistari" />
    <AdminLayout>
        
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Meistari</h1>
                <p class="text-white/50 text-sm mt-1">Visi reģistrētie meistari ({{ masters.total }})</p>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 max-w-sm">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="search"
                        @keydown.enter="doSearch"
                        type="text"
                        placeholder="Meklēt pēc vārda vai e-pasta..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                    />
                </div>
                <select
                    v-model="statusFilter"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                >
                    <option value="">Visi statusi</option>
                    <option value="active">Aktīvi</option>
                    <option value="suspended">Apturēti</option>
                </select>
                <button
                    @click="doSearch"
                    class="px-4 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy/90 transition-colors"
                >
                    Meklēt
                </button>
                <Link
                    v-if="hasFilters()"
                    :href="route('admin.masters.index')"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Notīrīt
                </Link>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Lietotājs</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Tips</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Pilsēta</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Pievienojās</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statuss</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Darbības</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="masters.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">
                                Nav atrasts neviens meistars.
                            </td>
                        </tr>
                        <tr
                            v-for="master in masters.data"
                            :key="master.id"
                            class="hover:bg-gray-50/60 transition-colors"
                        >
                            
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ master.name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ master.email }}</p>
                            </td>

                            
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="typeClass(master.profile?.type ?? null)"
                                >
                                    {{ typeLabel(master.profile?.type ?? null) }}
                                </span>
                            </td>

                            
                            <td class="px-6 py-4 text-gray-600 hidden md:table-cell">
                                {{ master.profile?.city ?? '-' }}
                            </td>

                            
                            <td class="px-6 py-4 text-gray-500 hidden md:table-cell">
                                {{ formatDate(master.created_at) }}
                            </td>

                            
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="master.profile?.is_verified
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-gray-100 text-gray-500'"
                                >
                                    <CheckBadgeIcon v-if="master.profile?.is_verified" class="w-3.5 h-3.5" />
                                    <XCircleIcon v-else class="w-3.5 h-3.5" />
                                    {{ master.profile?.is_verified ? 'Verificēts' : 'Neverificēts' }}
                                </span>
                            </td>

                            
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <Link
                                        :href="route('admin.masters.show', master.id)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-navy hover:bg-gray-100 transition-colors"
                                        title="Skatīt"
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                    </Link>
                                    <button
                                        @click="openEdit(master)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                        title="Rediģēt"
                                    >
                                        <PencilSquareIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="deleteTarget = master"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Dzēst"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            
            <div v-if="masters.last_page > 1" class="flex items-center justify-between text-sm text-gray-500">
                <p>
                    Rāda {{ masters.from }}-{{ masters.to }} no {{ masters.total }}
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in masters.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1.5 rounded-lg border transition-colors"
                            :class="link.active
                                ? 'bg-navy text-white border-navy font-semibold'
                                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 bg-white cursor-default"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        </div>

        
        <Modal :show="editModal" @close="editModal = false" maxWidth="md">
            <div class="bg-navy px-6 py-4 rounded-t-xl">
                <h2 class="text-base font-bold text-white">Rediģēt meistaru</h2>
                <p class="text-white/50 text-xs mt-0.5">{{ editTarget?.name }}</p>
            </div>
            <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Vārds</label>
                    <input
                        v-model="editForm.name"
                        type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                    />
                    <p v-if="editForm.errors.name" class="text-xs text-red-500 mt-1">{{ editForm.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">E-pasts</label>
                    <input
                        v-model="editForm.email"
                        type="email"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                    />
                    <p v-if="editForm.errors.email" class="text-xs text-red-500 mt-1">{{ editForm.errors.email }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="editForm.is_verified = !editForm.is_verified"
                        class="relative inline-flex h-5 w-9 shrink-0 rounded-full border-2 border-transparent transition-colors"
                        :class="editForm.is_verified ? 'bg-emerald-500' : 'bg-gray-200'"
                    >
                        <span
                            class="pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow transform transition-transform"
                            :class="editForm.is_verified ? 'translate-x-4' : 'translate-x-0'"
                        />
                    </button>
                    <label class="text-sm font-medium text-gray-700">Verificēts profils</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="editModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Atcelt
                    </button>
                    <button
                        type="submit"
                        :disabled="editForm.processing"
                        class="px-4 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy/90 transition-colors disabled:opacity-50"
                    >
                        Saglabāt
                    </button>
                </div>
            </form>
        </Modal>

        
        <ConfirmDialog
            :show="!!deleteTarget"
            title="Dzēst meistaru?"
            :message="`Vai tiešām vēlaties dzēst ${deleteTarget?.name}? Šo darbību nevar atcelt.`"
            confirmLabel="Dzēst"
            :processing="deleteProcessing"
            @confirm="doDelete"
            @cancel="deleteTarget = null"
        />
    </AdminLayout>
</template>
