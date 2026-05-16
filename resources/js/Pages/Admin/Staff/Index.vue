<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import Modal from '@/Components/Common/Modal.vue';
import {
    PencilSquareIcon,
    TrashIcon,
    PlusIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';

interface StaffMember {
    id: number;
    name: string;
    email: string;
    created_at: string;
    roles: { name: string }[];
}

interface PaginatedStaff {
    data: StaffMember[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

const props = defineProps<{
    staff: PaginatedStaff;
}>();

const isAdminMember = (member: StaffMember) =>
    member.roles.some(r => r.name === 'admin');

// Create modal
const createModal = ref(false);
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'moderator',
});

const submitCreate = () => {
    createForm.post(route('admin.staff.store'), {
        onSuccess: () => {
            createModal.value = false;
            createForm.reset();
        },
    });
};

// Edit modal
const editModal = ref(false);
const editTarget = ref<StaffMember | null>(null);
const editForm = useForm({ name: '', email: '' });

const openEdit = (member: StaffMember) => {
    editTarget.value = member;
    editForm.name = member.name;
    editForm.email = member.email;
    editModal.value = true;
};

const submitEdit = () => {
    if (!editTarget.value) return;
    editForm.put(route('admin.staff.update', editTarget.value.id), {
        onSuccess: () => { editModal.value = false; },
    });
};

// Delete confirm
const deleteTarget = ref<StaffMember | null>(null);
const deleteProcessing = ref(false);

const doDelete = () => {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(route('admin.staff.destroy', deleteTarget.value.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteTarget.value = null;
        },
    });
};

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });
</script>

<template>
    <AdminLayout>
        <!-- Page header -->
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">Darbinieki</h1>
                    <p class="text-white/50 text-sm mt-1">Administratori un moderatori ({{ staff.total }})</p>
                </div>
                <button
                    @click="createModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-navy bg-white rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <PlusIcon class="w-4 h-4" />
                    Pievienot moderatoru
                </button>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Lietotājs</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Loma</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Pievienojās</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Darbības</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="staff.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400">
                                Nav neviena darbinieka.
                            </td>
                        </tr>
                        <tr
                            v-for="member in staff.data"
                            :key="member.id"
                            class="hover:bg-gray-50/60 transition-colors"
                        >
                            <!-- Name + email -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                        :class="isAdminMember(member) ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600'">
                                        {{ member.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ member.name }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ member.email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Role badge -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                    :class="isAdminMember(member)
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-orange-100 text-orange-700'"
                                >
                                    {{ isAdminMember(member) ? 'Admins' : 'Moderators' }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-6 py-4 text-gray-500 hidden md:table-cell">
                                {{ formatDate(member.created_at) }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <template v-if="!isAdminMember(member)">
                                        <button
                                            @click="openEdit(member)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                            title="Rediģēt"
                                        >
                                            <PencilSquareIcon class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="deleteTarget = member"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Dzēst"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </template>
                                    <span v-else class="text-xs text-gray-300 pr-1">-</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="staff.last_page > 1" class="flex items-center justify-between text-sm text-gray-500">
                <p>Rāda {{ staff.from }}-{{ staff.to }} no {{ staff.total }}</p>
                <div class="flex items-center gap-1">
                    <template v-for="link in staff.links" :key="link.label">
                        <a
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

        <!-- Create modal -->
        <Modal :show="createModal" @close="createModal = false" maxWidth="md">
            <div class="bg-navy px-6 py-4 rounded-t-xl flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                    <UserGroupIcon class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Pievienot moderatoru</h2>
                    <p class="text-white/50 text-xs">Jaunais konts saņems moderatora tiesības</p>
                </div>
            </div>
            <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Vārds *</label>
                    <input
                        v-model="createForm.name"
                        type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                        placeholder="Pilns vārds"
                        required
                    />
                    <p v-if="createForm.errors.name" class="text-xs text-red-500 mt-1">{{ createForm.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">E-pasts *</label>
                    <input
                        v-model="createForm.email"
                        type="email"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                        placeholder="epasts@piemers.lv"
                        required
                    />
                    <p v-if="createForm.errors.email" class="text-xs text-red-500 mt-1">{{ createForm.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Parole *</label>
                    <input
                        v-model="createForm.password"
                        type="password"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                        placeholder="Vismaz 8 rakstzīmes"
                        required
                    />
                    <p v-if="createForm.errors.password" class="text-xs text-red-500 mt-1">{{ createForm.errors.password }}</p>
                </div>
                <div class="flex items-center gap-2 px-3 py-2 bg-orange-50 rounded-lg border border-orange-200">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
                        Moderators
                    </span>
                    <p class="text-xs text-gray-500">Administratora kontu var izveidot tikai tieši caur datu bāzi.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="createModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Atcelt
                    </button>
                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="px-4 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy/90 transition-colors disabled:opacity-50"
                    >
                        {{ createForm.processing ? 'Saglabā...' : 'Pievienot' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Edit modal -->
        <Modal :show="editModal" @close="editModal = false" maxWidth="md">
            <div class="bg-navy px-6 py-4 rounded-t-xl">
                <h2 class="text-base font-bold text-white">Rediģēt moderatoru</h2>
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

        <!-- Delete confirm -->
        <ConfirmDialog
            :show="!!deleteTarget"
            title="Dzēst moderatoru?"
            :message="`Vai tiešām vēlaties dzēst ${deleteTarget?.name}? Konts tiks deaktivizēts.`"
            confirmLabel="Dzēst"
            :processing="deleteProcessing"
            @confirm="doDelete"
            @cancel="deleteTarget = null"
        />
    </AdminLayout>
</template>
