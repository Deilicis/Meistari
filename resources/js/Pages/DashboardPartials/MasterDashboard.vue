<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { AuthUser, Service } from '@/types/models';
import {
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    PlusIcon,
    ChevronRightIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

defineProps<{
    stats: Record<string, number>;
    user: AuthUser;
    recentServices: Service[];
}>();

const formatPrice = (service: Service): string => {
    if (service.price_type === 'negotiable') return 'Vienojoties';
    if (!service.price) return '—';
    const formatted = new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(service.price);
    return service.price_type === 'hourly' ? `${formatted}/h` : formatted;
};
</script>

<template>
    <div class="space-y-6">

        <!-- Sveiciena josla -->
        <div class="bg-navy rounded-2xl overflow-hidden">
            <div class="h-1 w-full bg-gold" />
            <div class="px-8 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-gold text-xs font-bold tracking-widest uppercase mb-1">Meistara panelis</p>
                    <h2 class="text-2xl font-extrabold text-white">Sveicināts, {{ user.name }}!</h2>
                    <p class="text-white/50 text-sm mt-1">Pārvaldiet savus pakalpojumus un sekojiet līdzi pieteikumiem.</p>
                </div>
                <Link :href="route('master.services.index')"
                    class="inline-flex items-center gap-2 bg-gold text-navy text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-yellow-400 transition-colors shrink-0">
                    <PlusIcon class="w-4 h-4" stroke-width="2.5" />
                    Pievienot pakalpojumu
                </Link>
            </div>
        </div>

        <!-- Statistikas kartītes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                    <BriefcaseIcon class="w-6 h-6 text-navy" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Kopā pakalpojumi</p>
                    <p class="text-3xl font-extrabold text-navy leading-none">{{ stats.total_services ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ stats.active_services ?? 0 }} aktīvi</p>
                    <Link :href="route('master.services.index')"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-navy hover:text-navy-hover transition-colors">
                        Pārvaldīt
                        <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gold/10 flex items-center justify-center shrink-0">
                    <ClipboardDocumentListIcon class="w-6 h-6 text-gold" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Aktīvie pieteikumi</p>
                    <p class="text-3xl font-extrabold text-navy leading-none">{{ stats.active_applications ?? 0 }}</p>
                    <span class="inline-flex items-center mt-3 text-xs font-medium text-gray-300">
                        Drīzumā pieejams
                    </span>
                </div>
            </div>

        </div>

        <!-- Nesen pievienotie pakalpojumi -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900">Mani pakalpojumi</h3>
                <Link :href="route('master.services.index')"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-navy hover:text-navy-hover transition-colors">
                    Skatīt visus
                    <ChevronRightIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                </Link>
            </div>

            <!-- Tukšs stāvoklis -->
            <div v-if="recentServices.length === 0" class="px-6 py-10 text-center">
                <div class="mx-auto w-12 h-12 rounded-xl bg-navy/5 flex items-center justify-center mb-3">
                    <BriefcaseIcon class="w-6 h-6 text-navy/30" />
                </div>
                <p class="text-sm font-medium text-gray-600 mb-1">Nav neviena pakalpojuma</p>
                <p class="text-xs text-gray-400 mb-4">Izveidojiet savu pirmo pakalpojumu, lai klienti varētu jūs atrast.</p>
                <Link :href="route('master.services.index')"
                    class="inline-flex items-center gap-2 bg-navy text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-navy-hover transition-colors">
                    <PlusIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    Pievienot pakalpojumu
                </Link>
            </div>

            <!-- Pakalpojumu saraksts -->
            <ul v-else class="divide-y divide-gray-50">
                <li v-for="service in recentServices" :key="service.id"
                    class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/60 transition-colors">
                    <!-- Statusa ikona -->
                    <div class="shrink-0">
                        <CheckCircleIcon v-if="service.is_active" class="w-5 h-5 text-emerald-500" />
                        <XCircleIcon v-else class="w-5 h-5 text-gray-300" />
                    </div>

                    <!-- Informācija -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ service.title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ service.category?.name ?? '—' }}</p>
                    </div>

                    <!-- Cena -->
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-bold text-navy">{{ formatPrice(service) }}</p>
                        <p class="text-xs text-gray-400">
                            {{ service.is_active ? 'Aktīvs' : 'Neaktīvs' }}
                        </p>
                    </div>
                </li>
            </ul>
        </div>

    </div>
</template>