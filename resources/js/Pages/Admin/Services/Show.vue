<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmDialog from '@/Components/Common/ConfirmDialog.vue';
import AdminPagination from '@/Components/Common/AdminPagination.vue';
import {
    ArrowLeftIcon,
    EnvelopeIcon,
    MapPinIcon,
    CheckBadgeIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

interface Service {
    id: number;
    title: string;
    description: string;
    is_active: boolean;
    price: number | null;
    location: string[] | null;
    created_at: string;
    updated_at: string;
    category: { name: string } | null;
    user: {
        id: number;
        name: string;
        email: string;
        profile: {
            city: string | null;
            is_verified: boolean;
            avatar: string | null;
        } | null;
    };
}

interface ServiceApplication {
    id: number;
    message: string | null;
    budget_offer: number | null;
    status: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
        profile: { city: string | null; avatar: string | null } | null;
    };
}

interface Paginator<T> {
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
}

defineProps<{
    service: Service;
    applications: Paginator<ServiceApplication>;
}>();

const showDeleteConfirm = ref(false);
const deleteProcessing = ref(false);

const doDelete = (serviceId: number) => {
    deleteProcessing.value = true;
    router.delete(route('admin.services.destroy', serviceId), {
        onFinish: () => {
            deleteProcessing.value = false;
            showDeleteConfirm.value = false;
        },
    });
};

const formatDate = (d: string) =>
    new Date(d).toLocaleString('lv-LV', {
        day: '2-digit', month: '2-digit', year: 'numeric',
    });

const masterLink = (service: Service) =>
    route('admin.masters.show', service.user.id) + '?from_service_id=' + service.id;

const appStatusLabel = (s: string) => ({
    pending:   'Gaida',
    accepted:  'Pieņemts',
    rejected:  'Noraidīts',
    completed: 'Pabeigts',
    cancelled: 'Atcelts',
}[s] ?? s);

const appStatusClass = (s: string) => ({
    pending:   'bg-amber-100 text-amber-700',
    accepted:  'bg-emerald-100 text-emerald-700',
    rejected:  'bg-red-100 text-red-600',
    completed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-gray-100 text-gray-500',
}[s] ?? 'bg-gray-100 text-gray-600');

const truncate = (text: string | null, len = 100) => {
    if (!text) return '-';
    return text.length > len ? text.slice(0, len) + '...' : text;
};
</script>

<template>
    <Head :title="service.title" />
    <AdminLayout>
        
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <Link
                    :href="route('admin.services.index')"
                    class="inline-flex items-center gap-1.5 text-white/50 hover:text-white text-sm mb-4 transition-colors"
                >
                    <ArrowLeftIcon class="w-4 h-4" />
                    Pakalpojumi
                </Link>
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ service.title }}</h1>
                            <span
                                class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                :class="service.is_active ? 'bg-emerald-400/20 text-emerald-300' : 'bg-white/10 text-white/50'"
                            >
                                {{ service.is_active ? 'Aktīvs' : 'Neaktīvs' }}
                            </span>
                        </div>
                        <p class="text-white/50 text-sm mt-1">{{ service.category?.name ?? '-' }} · #{{ service.id }}</p>
                    </div>
                    <button
                        @click="showDeleteConfirm = true"
                        class="px-4 py-2 text-sm font-semibold text-red-400 border border-red-400/30 rounded-lg hover:bg-red-400/10 transition-colors"
                    >
                        Dzēst
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-navy px-6 py-4">
                        <h2 class="text-sm font-bold text-white">Detaļas</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-xs text-gray-400">Kategorija</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">{{ service.category?.name ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400">Cena</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">
                                <span v-if="service.price">€{{ service.price }}</span>
                                <span v-else class="text-gray-400">Vienojoties</span>
                            </p>
                        </div>

                        <div v-if="service.location?.length">
                            <p class="text-xs text-gray-400">Atrašanās vieta</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">{{ service.location.join(', ') }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400">Izveidots</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">{{ formatDate(service.created_at) }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400">Atjaunināts</p>
                            <p class="text-sm font-medium text-gray-900 mt-0.5">{{ formatDate(service.updated_at) }}</p>
                        </div>
                    </div>
                </div>

                
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-navy px-6 py-4">
                        <h2 class="text-sm font-bold text-white">Meistars</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-navy/10 flex items-center justify-center text-lg font-extrabold text-navy shrink-0 overflow-hidden">
                                <img
                                    v-if="service.user.profile?.avatar"
                                    :src="`/storage/${service.user.profile.avatar}`"
                                    class="w-12 h-12 rounded-full object-cover"
                                    :alt="service.user.name"
                                />
                                <span v-else>{{ service.user.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="masterLink(service)"
                                    class="text-base font-semibold text-navy hover:underline"
                                >
                                    {{ service.user.name }}
                                </Link>
                                <div class="flex items-center gap-3 mt-1 flex-wrap">
                                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <EnvelopeIcon class="w-3.5 h-3.5 text-gray-400" />
                                        {{ service.user.email }}
                                    </span>
                                    <span v-if="service.user.profile?.city" class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <MapPinIcon class="w-3.5 h-3.5 text-gray-400" />
                                        {{ service.user.profile.city }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="service.user.profile?.is_verified
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-gray-100 text-gray-500'"
                                    >
                                        <CheckBadgeIcon v-if="service.user.profile?.is_verified" class="w-3.5 h-3.5" />
                                        <XCircleIcon v-else class="w-3.5 h-3.5" />
                                        {{ service.user.profile?.is_verified ? 'Verificēts' : 'Neverificēts' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-navy px-6 py-4">
                    <h2 class="text-sm font-bold text-white">Apraksts</h2>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ service.description }}</p>
                </div>
            </div>


            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-navy px-6 py-4">
                    <h2 class="text-sm font-bold text-white">
                        Pieteikumi ({{ applications.total }})
                    </h2>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Pieteicējs</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Piedāvājums</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Ziņa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statuss</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Datums</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="applications.data.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">
                                Nav pieteikumu.
                            </td>
                        </tr>
                        <tr
                            v-for="app in applications.data"
                            :key="app.id"
                            class="hover:bg-gray-50/60 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <Link
                                    :href="route('admin.seekers.show', app.user.id)"
                                    class="font-semibold text-navy hover:underline"
                                >
                                    {{ app.user.name }}
                                </Link>
                                <p class="text-xs text-gray-400 mt-0.5">{{ app.user.email }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-700 hidden md:table-cell">
                                <span v-if="app.budget_offer">€{{ app.budget_offer }}</span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 hidden lg:table-cell max-w-xs">
                                {{ truncate(app.message) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="appStatusClass(app.status)"
                                >
                                    {{ appStatusLabel(app.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 hidden md:table-cell whitespace-nowrap">
                                {{ formatDate(app.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <AdminPagination v-if="applications.last_page > 1" :links="applications.links" />
            </div>

        </div>

        
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="Dzēst pakalpojumu?"
            :message="`Vai tiešām vēlaties dzēst pakalpojumu &quot;${service.title}&quot;? Šo darbību nevar atcelt.`"
            confirmLabel="Dzēst"
            :processing="deleteProcessing"
            @confirm="doDelete(service.id)"
            @cancel="showDeleteConfirm = false"
        />
    </AdminLayout>
</template>
