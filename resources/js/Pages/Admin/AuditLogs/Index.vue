<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import AuditLogSearchBar from '@/Components/Search/AuditLogSearchBar.vue';

interface AuditLog {
    id: number;
    action: 'created' | 'updated' | 'deleted';
    auditable_type: string;
    auditable_id: number;
    old_values: Record<string, any> | null;
    new_values: Record<string, any> | null;
    ip_address: string | null;
    created_at: string;
    user: { id: number; name: string } | null;
}

interface PaginatedLogs {
    data: AuditLog[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

const props = defineProps<{
    auditLogs: PaginatedLogs;
    filters: { action?: string; user_id?: string; type?: string };
}>();

const expandedId = ref<number | null>(null);
const toggleExpand = (id: number) => {
    expandedId.value = expandedId.value === id ? null : id;
};

const HIDDEN_FIELDS = new Set(['password', 'remember_token']);

const modelName = (type: string) => type.split('\\').pop() ?? type;

const actionBadgeClass = (action: string) => ({
    created: 'bg-emerald-100 text-emerald-700',
    updated: 'bg-amber-100 text-amber-700',
    deleted: 'bg-red-100 text-red-700',
}[action] ?? 'bg-gray-100 text-gray-600');

const actionLabel = (action: string) => ({
    created: 'Izveidots',
    updated: 'Atjaunināts',
    deleted: 'Dzēsts',
}[action] ?? action);

const changeCount = (log: AuditLog): string => {
    const vals = log.action === 'updated' ? log.new_values : (log.new_values ?? log.old_values);
    if (!vals) return '-';
    const count = Object.keys(vals).filter(k => !HIDDEN_FIELDS.has(k)).length;
    return `${count} ${count === 1 ? 'lauks' : 'lauki'}`;
};

const filteredValues = (vals: Record<string, any> | null) => {
    if (!vals) return {};
    return Object.fromEntries(Object.entries(vals).filter(([k]) => !HIDDEN_FIELDS.has(k)));
};

const formatDate = (d: string) =>
    new Date(d).toLocaleString('lv-LV', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });

const formatValue = (v: any): string => {
    if (v === null || v === undefined) return '-';
    if (typeof v === 'boolean') return v ? 'Jā' : 'Nē';
    if (typeof v === 'object') return JSON.stringify(v);
    return String(v);
};
</script>

<template>
    <Head title="Audita žurnāls" />
    <AdminLayout>
        
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Audita žurnāls</h1>
                <p class="text-white/50 text-sm mt-1">Visi reģistrētie sistēmas notikumi ({{ auditLogs.total }})</p>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            
            <AuditLogSearchBar :filters="filters" />

            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-6"></th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Darbinieks</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Darbība</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Objekts</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Izmaiņas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">IP adrese</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Laiks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="auditLogs.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">
                                Nav audita ierakstu.
                            </td>
                        </tr>

                        <template v-for="log in auditLogs.data" :key="log.id">
                            
                            <tr
                                class="border-t border-gray-50 hover:bg-gray-50/60 transition-colors cursor-pointer"
                                @click="toggleExpand(log.id)"
                            >
                                
                                <td class="px-4 py-3 text-gray-300">
                                    <ChevronDownIcon v-if="expandedId === log.id" class="w-4 h-4 text-navy/40" />
                                    <ChevronRightIcon v-else class="w-4 h-4" />
                                </td>

                                
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-900">{{ log.user?.name ?? 'Sistēma' }}</span>
                                </td>

                                
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="actionBadgeClass(log.action)"
                                    >
                                        {{ actionLabel(log.action) }}
                                    </span>
                                </td>

                                
                                <td class="px-4 py-3 text-gray-700">
                                    <span class="font-medium">{{ modelName(log.auditable_type) }}</span>
                                    <span class="text-gray-400 text-xs ml-1">#{{ log.auditable_id }}</span>
                                </td>

                                
                                <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                                    {{ changeCount(log) }}
                                </td>

                                
                                <td class="px-4 py-3 text-gray-400 font-mono text-xs hidden lg:table-cell">
                                    {{ log.ip_address ?? '-' }}
                                </td>

                                
                                <td class="px-4 py-3 text-gray-400 text-xs hidden md:table-cell whitespace-nowrap">
                                    {{ formatDate(log.created_at) }}
                                </td>
                            </tr>

                            
                            <tr v-if="expandedId === log.id" class="bg-gray-50/80 border-t border-gray-100">
                                <td colspan="7" class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                        
                                        <div v-if="log.action !== 'created' && log.old_values">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Iepriekšējās vērtības</p>
                                            <div class="space-y-1">
                                                <div
                                                    v-for="(val, key) in filteredValues(log.old_values)"
                                                    :key="key"
                                                    class="flex items-start gap-2 text-xs"
                                                >
                                                    <span class="font-mono text-gray-500 shrink-0 min-w-32">{{ key }}</span>
                                                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded font-mono break-all">{{ formatValue(val) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div v-if="log.action !== 'deleted' && log.new_values">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                                {{ log.action === 'created' ? 'Vērtības' : 'Jaunās vērtības' }}
                                            </p>
                                            <div class="space-y-1">
                                                <div
                                                    v-for="(val, key) in filteredValues(log.new_values)"
                                                    :key="key"
                                                    class="flex items-start gap-2 text-xs"
                                                >
                                                    <span class="font-mono text-gray-500 shrink-0 min-w-32">{{ key }}</span>
                                                    <span class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-mono break-all">{{ formatValue(val) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div v-if="log.action === 'deleted' && log.old_values">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Dzēstās vērtības</p>
                                            <div class="space-y-1">
                                                <div
                                                    v-for="(val, key) in filteredValues(log.old_values)"
                                                    :key="key"
                                                    class="flex items-start gap-2 text-xs"
                                                >
                                                    <span class="font-mono text-gray-500 shrink-0 min-w-32">{{ key }}</span>
                                                    <span class="text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded font-mono break-all">{{ formatValue(val) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            
            <div v-if="auditLogs.last_page > 1" class="flex items-center justify-between text-sm text-gray-500">
                <p>Rāda {{ auditLogs.from }}-{{ auditLogs.to }} no {{ auditLogs.total }}</p>
                <div class="flex items-center gap-1">
                    <template v-for="link in auditLogs.links" :key="link.label">
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
    </AdminLayout>
</template>
