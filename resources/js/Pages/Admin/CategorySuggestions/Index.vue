<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    TagIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowsRightLeftIcon,
    ClockIcon,
    UserIcon,
    FolderIcon,
    ChatBubbleLeftIcon,
} from '@heroicons/vue/24/outline';

// ─── Types ────────────────────────────────────────────────────────────────────

interface CategoryOption {
    id: number;
    name: string;
    children: { id: number; name: string }[];
}

interface Suggestion {
    id: number;
    name: string;
    note: string | null;
    status: 'pending' | 'approved' | 'rejected' | 'merged';
    status_label: string;
    parent_category: { id: number; name: string } | null;
    suggested_by: { id: number; name: string } | null;
    reviewed_by: { id: number; name: string } | null;
    review_note: string | null;
    resulting_category: { id: number; name: string } | null;
    services_count: number;
    job_requests_count: number;
    created_at: string | null;
    reviewed_at: string | null;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    pending:    Suggestion[];
    resolved:   Suggestion[];
    categories: CategoryOption[];
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const activeTab  = ref<'pending' | 'resolved'>('pending');
const processing = ref(false);

const actingSuggestion = ref<Suggestion | null>(null);
const showApprove = ref(false);
const showReject  = ref(false);
const showMerge   = ref(false);

const approveIcon     = ref('');
const rejectNote      = ref('');
const mergeTargetId   = ref<number | null>(null);

// ─── Local list copies (so we can remove items on success without full page reload) ─

const pendingList   = ref<Suggestion[]>([...props.pending]);
const resolvedList  = ref<Suggestion[]>([...props.resolved]);

// ─── Computed ─────────────────────────────────────────────────────────────────

const flatCategories = computed(() => {
    const out: { id: number; label: string }[] = [];
    for (const cat of props.categories) {
        out.push({ id: cat.id, label: cat.name });
        for (const child of cat.children) {
            out.push({ id: child.id, label: `${cat.name} / ${child.name}` });
        }
    }
    return out;
});

// ─── Modal openers ────────────────────────────────────────────────────────────

function openApprove(s: Suggestion) {
    actingSuggestion.value = s;
    approveIcon.value      = '';
    showApprove.value      = true;
}

function openReject(s: Suggestion) {
    actingSuggestion.value = s;
    rejectNote.value       = '';
    showReject.value       = true;
}

function openMerge(s: Suggestion) {
    actingSuggestion.value = s;
    mergeTargetId.value    = null;
    showMerge.value        = true;
}

function closeAll() {
    showApprove.value = false;
    showReject.value  = false;
    showMerge.value   = false;
    actingSuggestion.value = null;
}

// ─── Actions ──────────────────────────────────────────────────────────────────

async function submitApprove() {
    if (!actingSuggestion.value || processing.value) return;
    processing.value = true;
    const id = actingSuggestion.value.id;
    try {
        await axios.post(route('admin.category-suggestions.approve', id), {
            icon: approveIcon.value || null,
        });
        toast.success('Priekšlikums apstiprināts.');
        pendingList.value = pendingList.value.filter(s => s.id !== id);
        closeAll();
    } catch {
        toast.error('Neizdevās apstiprināt priekšlikumu.');
    } finally {
        processing.value = false;
    }
}

async function submitReject() {
    if (!actingSuggestion.value || processing.value) return;
    if (!rejectNote.value.trim()) return;
    processing.value = true;
    const id = actingSuggestion.value.id;
    try {
        await axios.post(route('admin.category-suggestions.reject', id), {
            review_note: rejectNote.value.trim(),
        });
        toast.success('Priekšlikums noraidīts.');
        pendingList.value = pendingList.value.filter(s => s.id !== id);
        closeAll();
    } catch {
        toast.error('Neizdevās noraidīt priekšlikumu.');
    } finally {
        processing.value = false;
    }
}

async function submitMerge() {
    if (!actingSuggestion.value || processing.value || !mergeTargetId.value) return;
    processing.value = true;
    const id = actingSuggestion.value.id;
    try {
        await axios.post(route('admin.category-suggestions.merge', id), {
            target_category_id: mergeTargetId.value,
        });
        toast.success('Priekšlikums apvienots.');
        pendingList.value = pendingList.value.filter(s => s.id !== id);
        closeAll();
    } catch {
        toast.error('Neizdevās apvienot priekšlikumu.');
    } finally {
        processing.value = false;
    }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(iso: string | null): string {
    if (!iso) return '-';
    return new Date(iso).toLocaleDateString('lv-LV', { year: 'numeric', month: 'short', day: 'numeric' });
}

const statusBadge: Record<string, { label: string; cls: string }> = {
    pending:  { label: 'Gaida',      cls: 'bg-amber-100 text-amber-700' },
    approved: { label: 'Apstiprināts', cls: 'bg-emerald-100 text-emerald-700' },
    rejected: { label: 'Noraidīts',  cls: 'bg-red-100 text-red-600' },
    merged:   { label: 'Apvienots',  cls: 'bg-blue-100 text-blue-700' },
};
</script>

<template>
    <Head title="Kategoriju priekšlikumi" />
    <AdminLayout>
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center">
                    <TagIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-gray-900">Kategoriju priekšlikumi</h1>
                    <p class="text-sm text-gray-500">Izskatīt lietotāju ieteiktās kategorijas</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 mb-6 border-b border-gray-200">
                <button
                    @click="activeTab = 'pending'"
                    class="px-4 py-2 text-sm font-semibold transition-colors border-b-2 -mb-px"
                    :class="activeTab === 'pending'
                        ? 'border-navy text-navy'
                        : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    Gaidošie
                    <span
                        v-if="pendingList.length > 0"
                        class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700"
                    >{{ pendingList.length }}</span>
                </button>
                <button
                    @click="activeTab = 'resolved'"
                    class="px-4 py-2 text-sm font-semibold transition-colors border-b-2 -mb-px"
                    :class="activeTab === 'resolved'
                        ? 'border-navy text-navy'
                        : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    Izskatītie
                </button>
            </div>

            <!-- ─── Pending tab ──────────────────────────────────────────────── -->
            <div v-if="activeTab === 'pending'">
                <div v-if="pendingList.length === 0" class="text-center py-16 text-gray-400">
                    <TagIcon class="w-10 h-10 mx-auto mb-3 text-gray-200" />
                    <p class="text-sm font-medium">Nav gaidošu priekšlikumu</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="s in pendingList"
                        :key="s.id"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-base font-bold text-gray-900">{{ s.name }}</h2>
                                    <span v-if="s.parent_category" class="text-xs text-gray-400 flex items-center gap-1">
                                        <FolderIcon class="w-3.5 h-3.5" />
                                        apakš "{{ s.parent_category.name }}"
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <UserIcon class="w-3.5 h-3.5" />
                                        {{ s.suggested_by?.name ?? '-' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <ClockIcon class="w-3.5 h-3.5" />
                                        {{ formatDate(s.created_at) }}
                                    </span>
                                    <span v-if="s.services_count + s.job_requests_count > 0">
                                        {{ s.services_count }} pak. + {{ s.job_requests_count }} sl.
                                    </span>
                                </div>

                                <p v-if="s.note" class="mt-2 text-sm text-gray-600 flex items-start gap-1.5">
                                    <ChatBubbleLeftIcon class="w-4 h-4 mt-0.5 shrink-0 text-gray-400" />
                                    {{ s.note }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    @click="openApprove(s)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors"
                                >
                                    <CheckCircleIcon class="w-3.5 h-3.5" />
                                    Apstiprināt
                                </button>
                                <button
                                    @click="openMerge(s)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors"
                                >
                                    <ArrowsRightLeftIcon class="w-3.5 h-3.5" />
                                    Apvienot
                                </button>
                                <button
                                    @click="openReject(s)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors"
                                >
                                    <XCircleIcon class="w-3.5 h-3.5" />
                                    Noraidīt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── Resolved tab ─────────────────────────────────────────────── -->
            <div v-if="activeTab === 'resolved'">
                <div v-if="resolvedList.length === 0" class="text-center py-16 text-gray-400">
                    <TagIcon class="w-10 h-10 mx-auto mb-3 text-gray-200" />
                    <p class="text-sm font-medium">Nav izskatītu priekšlikumu</p>
                </div>

                <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Priekšlikums</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pieteicējs</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Statuss</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Rezultāts</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Izskatīts</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="s in resolvedList" :key="s.id" class="hover:bg-gray-50/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ s.name }}</div>
                                    <div v-if="s.parent_category" class="text-xs text-gray-400">apakš "{{ s.parent_category.name }}"</div>
                                    <div v-if="s.review_note" class="text-xs text-gray-500 mt-0.5 italic">{{ s.review_note }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ s.suggested_by?.name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="statusBadge[s.status]?.cls"
                                    >{{ statusBadge[s.status]?.label }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ s.resulting_category?.name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-400 text-xs">{{ formatDate(s.reviewed_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ─── Approve modal ──────────────────────────────────────────────────── -->
        <Teleport to="body">
            <div
                v-if="showApprove && actingSuggestion"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                @click.self="closeAll"
            >
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                    <div class="bg-navy px-5 py-4 rounded-t-2xl flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-emerald-400" />
                            <h3 class="text-base font-bold text-white">Apstiprināt priekšlikumu</h3>
                        </div>
                        <button @click="closeAll" class="text-white/60 hover:text-white transition-colors">
                            <XCircleIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <p class="text-sm text-gray-600">
                            Tiks izveidota jauna kategorija
                            <span class="font-semibold text-gray-900">"{{ actingSuggestion.name }}"</span>
                            <span v-if="actingSuggestion.parent_category"> apakš "{{ actingSuggestion.parent_category.name }}"</span>.
                            Visi sludinājumi tiks pārvietoti uz šo kategoriju.
                        </p>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Ikona (neobligāti)</label>
                            <input
                                v-model="approveIcon"
                                type="text"
                                placeholder="Piem., wrench-screwdriver"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:ring-1 focus:ring-navy outline-none"
                            />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                @click="closeAll"
                                class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:border-gray-300 transition-colors"
                            >Atcelt</button>
                            <button
                                @click="submitApprove"
                                :disabled="processing"
                                class="px-5 py-2 text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded-xl transition-colors disabled:opacity-60"
                            >{{ processing ? 'Apstiprina...' : 'Apstiprināt' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ─── Reject modal ───────────────────────────────────────────────────── -->
        <Teleport to="body">
            <div
                v-if="showReject && actingSuggestion"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                @click.self="closeAll"
            >
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                    <div class="bg-navy px-5 py-4 rounded-t-2xl flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <XCircleIcon class="w-5 h-5 text-red-400" />
                            <h3 class="text-base font-bold text-white">Noraidīt priekšlikumu</h3>
                        </div>
                        <button @click="closeAll" class="text-white/60 hover:text-white transition-colors">
                            <XCircleIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <p class="text-sm text-gray-600">
                            Noraidīt priekšlikumu
                            <span class="font-semibold text-gray-900">"{{ actingSuggestion.name }}"</span>.
                            Sludinājumi paliks kategorijā "Cits". Pieteicējs saņems paziņojumu ar noraidījuma iemeslu.
                        </p>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Noraidījuma iemesls <span class="text-red-500">*</span></label>
                            <textarea
                                v-model="rejectNote"
                                rows="3"
                                placeholder="Paskaidro, kāpēc priekšlikums netiek pieņemts..."
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:ring-1 focus:ring-navy outline-none resize-none"
                            />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                @click="closeAll"
                                class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:border-gray-300 transition-colors"
                            >Atcelt</button>
                            <button
                                @click="submitReject"
                                :disabled="processing || !rejectNote.trim()"
                                class="px-5 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors disabled:opacity-60"
                            >{{ processing ? 'Noraida...' : 'Noraidīt' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ─── Merge modal ────────────────────────────────────────────────────── -->
        <Teleport to="body">
            <div
                v-if="showMerge && actingSuggestion"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                @click.self="closeAll"
            >
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                    <div class="bg-navy px-5 py-4 rounded-t-2xl flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <ArrowsRightLeftIcon class="w-5 h-5 text-blue-400" />
                            <h3 class="text-base font-bold text-white">Apvienot ar esošo kategoriju</h3>
                        </div>
                        <button @click="closeAll" class="text-white/60 hover:text-white transition-colors">
                            <XCircleIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <p class="text-sm text-gray-600">
                            Priekšlikums
                            <span class="font-semibold text-gray-900">"{{ actingSuggestion.name }}"</span>
                            tiks apvienots ar izvēlēto kategoriju. Visi sludinājumi tiks pārvietoti.
                        </p>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Mērķa kategorija <span class="text-red-500">*</span></label>
                            <select
                                v-model="mergeTargetId"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-navy focus:ring-1 focus:ring-navy outline-none"
                            >
                                <option :value="null" disabled>Izvēlies kategoriju...</option>
                                <option
                                    v-for="cat in flatCategories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >{{ cat.label }}</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                @click="closeAll"
                                class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:border-gray-300 transition-colors"
                            >Atcelt</button>
                            <button
                                @click="submitMerge"
                                :disabled="processing || !mergeTargetId"
                                class="px-5 py-2 text-sm font-bold text-white bg-blue-500 hover:bg-blue-600 rounded-xl transition-colors disabled:opacity-60"
                            >{{ processing ? 'Apvieno...' : 'Apvienot' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
