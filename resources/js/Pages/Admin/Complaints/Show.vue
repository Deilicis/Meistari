<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ShieldExclamationIcon } from '@heroicons/vue/24/outline';

interface Profile {
    first_name?: string | null;
    last_name?: string | null;
    company_name?: string | null;
    type?: string | null;
    city?: string | null;
}

interface ComplaintUser {
    id: number;
    name: string;
    profile: Profile | null;
}

interface ReportedEntity {
    id: number;
    title?: string;
}

interface Complaint {
    id: number;
    reporter: ComplaintUser;
    reported_user: ComplaintUser;
    reported_entity_type: string | null;
    reported_entity_id: number | null;
    reported_entity: ReportedEntity | null;
    reason: string;
    status: string;
    resolved_by: ComplaintUser | null;
    resolution_note: string | null;
    resolved_at: string | null;
    created_at: string;
}

const props = defineProps<{
    complaint: Complaint;
}>();

type StatusKey = 'pending' | 'reviewed' | 'resolved' | 'dismissed';

const statusConfig: Record<StatusKey, { label: string; classes: string }> = {
    pending:   { label: 'Gaida',       classes: 'bg-amber-100 text-amber-800 ring-1 ring-amber-200' },
    reviewed:  { label: 'Izskatīts',   classes: 'bg-blue-100 text-blue-800 ring-1 ring-blue-200' },
    resolved:  { label: 'Atrisināts',  classes: 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200' },
    dismissed: { label: 'Noraidīts',   classes: 'bg-gray-100 text-gray-600 ring-1 ring-gray-200' },
};

const resolutionStatus = ref<string>(
    props.complaint.status === 'pending' || props.complaint.status === 'reviewed'
        ? props.complaint.status
        : props.complaint.status
);
const resolutionNote = ref(props.complaint.resolution_note ?? '');
const saving = ref(false);

const saveResolution = () => {
    saving.value = true;
    router.put(route('admin.complaints.update', props.complaint.id), {
        status: resolutionStatus.value,
        resolution_note: resolutionNote.value.trim() || null,
    }, {
        onSuccess: () => { toast.success('Sūdzība atjaunināta.'); },
        onError:   (errors) => { toast.error(Object.values(errors)[0] ?? 'Neizdevās saglabāt.'); },
        onFinish:  () => { saving.value = false; },
    });
};

const isResolved = () => props.complaint.status === 'resolved' || props.complaint.status === 'dismissed';

const displayName = (user: ComplaintUser): string => {
    const p = user.profile;
    if (!p) return user.name;
    if (p.type === 'company') return p.company_name ?? user.name;
    const parts = [p.first_name, p.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : user.name;
};

const initials = (user: ComplaintUser): string => displayName(user).slice(0, 2).toUpperCase();

const entityLabel = (type: string | null): string => {
    if (!type) return '';
    if (type.includes('Service')) return 'Pakalpojums';
    if (type.includes('JobRequest')) return 'Sludinājums';
    return 'Entitija';
};

const entityRoute = (type: string | null, id: number | null): string | null => {
    if (!type || !id) return null;
    if (type.includes('Service')) return route('admin.services.show', id);
    if (type.includes('JobRequest')) return route('admin.job-requests.show', id);
    return null;
};

const formatDate = (d: string | null) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('lv-LV', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AdminLayout>
        <div class="bg-navy">
            <div class="h-1 bg-rose-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <Link
                            :href="route('admin.complaints.index')"
                            class="inline-flex items-center gap-1 text-white/60 hover:text-white text-sm mb-3 transition-colors"
                        >
                            ← Sūdzības
                        </Link>
                        <div class="flex items-center gap-3">
                            <ShieldExclamationIcon class="w-6 h-6 text-rose-400" />
                            <div>
                                <h1 class="text-2xl font-extrabold text-white tracking-tight">Sūdzība #{{ complaint.id }}</h1>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                :class="statusConfig[complaint.status as StatusKey]?.classes ?? statusConfig.pending.classes"
                            >
                                {{ statusConfig[complaint.status as StatusKey]?.label ?? complaint.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Informācija</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Iesniedzējs</p>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-navy/10 flex items-center justify-center text-navy font-bold text-sm flex-shrink-0">
                                    {{ initials(complaint.reporter) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ displayName(complaint.reporter) }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Iesniegts: {{ formatDate(complaint.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                        
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Par lietotāju</p>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 font-bold text-sm flex-shrink-0">
                                    {{ initials(complaint.reported_user) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ displayName(complaint.reported_user) }}</p>
                                    <p v-if="complaint.reported_user.profile?.city" class="text-xs text-gray-400 mt-0.5">{{ complaint.reported_user.profile.city }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div v-if="complaint.reported_entity_type" class="mb-6 pb-6 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Par ko</p>
                        <div>
                            <template v-if="entityRoute(complaint.reported_entity_type, complaint.reported_entity_id)">
                                <Link
                                    :href="entityRoute(complaint.reported_entity_type, complaint.reported_entity_id)!"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-navy hover:underline"
                                >
                                    {{ entityLabel(complaint.reported_entity_type) }} #{{ complaint.reported_entity_id }}
                                    <span v-if="complaint.reported_entity?.title" class="text-gray-500 font-normal">- {{ complaint.reported_entity.title }}</span>
                                </Link>
                            </template>
                            <span v-else class="text-sm text-gray-600">
                                {{ entityLabel(complaint.reported_entity_type) }} #{{ complaint.reported_entity_id }}
                            </span>
                        </div>
                    </div>

                    
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Iemesls</p>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ complaint.reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Atrisināšana</h2>
                </div>

                
                <div v-if="isResolved()" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Atrisināts</p>
                            <p class="text-sm text-gray-700">{{ formatDate(complaint.resolved_at) }}</p>
                        </div>
                        <div v-if="complaint.resolved_by">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Atrisināja</p>
                            <p class="text-sm text-gray-700">{{ displayName(complaint.resolved_by) }}</p>
                        </div>
                    </div>
                    <div v-if="complaint.resolution_note">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Piezīme</p>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ complaint.resolution_note }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-400 italic">Nav piezīmju.</p>
                </div>

                
                <div v-else class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Statuss</label>
                        <select
                            v-model="resolutionStatus"
                            class="w-full max-w-xs px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy"
                        >
                            <option value="reviewed">Izskatīts</option>
                            <option value="resolved">Atrisināts</option>
                            <option value="dismissed">Noraidīts</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Piezīme <span class="font-normal text-gray-400">(neobligāti)</span></label>
                        <textarea
                            v-model="resolutionNote"
                            rows="3"
                            placeholder="Papildu komentārs par lēmumu..."
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-none"
                        />
                    </div>
                    <div class="flex items-center justify-end">
                        <button
                            type="button"
                            @click="saveResolution"
                            :disabled="saving"
                            class="px-4 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy/90 transition-colors disabled:opacity-50"
                        >
                            Saglabāt
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
