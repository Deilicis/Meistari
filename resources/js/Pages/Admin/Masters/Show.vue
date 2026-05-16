<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ArrowLeftIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    CheckBadgeIcon,
    XCircleIcon,
    StarIcon,
    BriefcaseIcon,
} from '@heroicons/vue/24/outline';

interface Experience {
    title: string;
    years: number | string | null;
    description: string | null;
}

interface Service {
    id: number;
    title: string;
    is_active: boolean;
    price: number | null;
    price_type: string;
    created_at: string;
    category: { name: string } | null;
}

interface Review {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    reviewer: {
        id: number;
        name: string;
        profile: { avatar: string | null } | null;
    };
}

interface Master {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    profile: {
        city: string | null;
        phone: string | null;
        bio: string | null;
        is_verified: boolean;
        avatar: string | null;
        type: 'individual' | 'company' | null;
        first_name: string | null;
        last_name: string | null;
        company_name: string | null;
        experiences: Experience[];
        portfolio_images: string[];
    } | null;
    services: Service[];
    reviews_received: Review[];
}

defineProps<{ master: Master }>();

const fromServiceId = computed(() =>
    new URLSearchParams(window.location.search).get('from_service_id')
);

const priceTypeLabel = (type: string) => {
    const map: Record<string, string> = {
        fixed: 'Fiksēta',
        hourly: '/stundā',
        negotiable: 'Vienojoties',
    };
    return map[type] ?? type;
};

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit', year: 'numeric' });

const typeLabel = (type: string | null) => type === 'company' ? 'Uzņēmums' : 'Privātpersona';
const typeClass = (type: string | null) => type === 'company'
    ? 'bg-blue-100 text-blue-700'
    : 'bg-gray-100 text-gray-600';
</script>

<template>
    <AdminLayout>
        <!-- Header -->
        <div class="bg-navy">
            <div class="h-1 bg-red-500" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-4 mb-4">
                    <Link
                        v-if="fromServiceId"
                        :href="route('admin.services.show', fromServiceId)"
                        class="inline-flex items-center gap-1.5 text-white/50 hover:text-white text-sm transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        Atpakaļ uz pakalpojumu
                    </Link>
                    <Link
                        :href="route('admin.masters.index')"
                        class="inline-flex items-center gap-1.5 text-white/50 hover:text-white text-sm transition-colors"
                    >
                        <ArrowLeftIcon v-if="!fromServiceId" class="w-4 h-4" />
                        {{ fromServiceId ? 'Meistari' : 'Atpakaļ uz meistariem' }}
                    </Link>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center text-xl font-extrabold text-white shrink-0 overflow-hidden">
                        <img
                            v-if="master.profile?.avatar"
                            :src="`/storage/${master.profile.avatar}`"
                            class="w-14 h-14 rounded-full object-cover"
                            :alt="master.name"
                        />
                        <span v-else>{{ master.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ master.name }}</h1>
                            <span
                                class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                :class="typeClass(master.profile?.type ?? null)"
                            >
                                {{ typeLabel(master.profile?.type ?? null) }}
                            </span>
                        </div>
                        <p class="text-white/50 text-sm mt-0.5">Reģistrēts: {{ formatDate(master.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Profile info -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-navy px-6 py-4">
                        <h2 class="text-sm font-bold text-white">Profila informācija</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <EnvelopeIcon class="w-4 h-4 text-gray-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-400">E-pasts</p>
                                <p class="text-sm font-medium text-gray-900">{{ master.email }}</p>
                            </div>
                        </div>

                        <div v-if="master.profile?.phone" class="flex items-center gap-3">
                            <PhoneIcon class="w-4 h-4 text-gray-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-400">Tālrunis</p>
                                <p class="text-sm font-medium text-gray-900">{{ master.profile.phone }}</p>
                            </div>
                        </div>

                        <div v-if="master.profile?.city" class="flex items-center gap-3">
                            <MapPinIcon class="w-4 h-4 text-gray-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-400">Pilsēta</p>
                                <p class="text-sm font-medium text-gray-900">{{ master.profile.city }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <component
                                :is="master.profile?.is_verified ? CheckBadgeIcon : XCircleIcon"
                                class="w-4 h-4 shrink-0"
                                :class="master.profile?.is_verified ? 'text-emerald-500' : 'text-gray-400'"
                            />
                            <div>
                                <p class="text-xs text-gray-400">Verifikācija</p>
                                <p class="text-sm font-medium"
                                    :class="master.profile?.is_verified ? 'text-emerald-600' : 'text-gray-500'">
                                    {{ master.profile?.is_verified ? 'Verificēts' : 'Neverificēts' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="master.profile?.bio" class="pt-2 border-t border-gray-50">
                            <p class="text-xs text-gray-400 mb-1">Par sevi</p>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ master.profile.bio }}</p>
                        </div>

                        <!-- Experiences -->
                        <div v-if="master.profile?.experiences?.length" class="pt-2 border-t border-gray-50">
                            <p class="text-xs text-gray-400 mb-2 flex items-center gap-1">
                                <BriefcaseIcon class="w-3.5 h-3.5" />
                                Pieredze
                            </p>
                            <ul class="space-y-2">
                                <li
                                    v-for="(exp, i) in master.profile.experiences"
                                    :key="i"
                                    class="text-sm"
                                >
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-gray-800">{{ exp.title }}</span>
                                        <span v-if="exp.years" class="text-xs bg-navy/10 text-navy px-2 py-0.5 rounded-full font-medium">
                                            {{ exp.years }} {{ Number(exp.years) === 1 ? 'gads' : 'gadi' }}
                                        </span>
                                    </div>
                                    <p v-if="exp.description" class="text-xs text-gray-500 mt-0.5">{{ exp.description }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Services -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-navy px-6 py-4">
                        <h2 class="text-sm font-bold text-white">
                            Pakalpojumi ({{ master.services.length }})
                        </h2>
                    </div>

                    <div v-if="master.services.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                        Nav pakalpojumu.
                    </div>

                    <ul v-else class="divide-y divide-gray-50">
                        <li
                            v-for="service in master.services"
                            :key="service.id"
                            class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50/60 transition-colors"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ service.title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ service.category?.name ?? '-' }} · {{ formatDate(service.created_at) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span v-if="service.price" class="text-sm font-semibold text-gray-700">
                                    €{{ service.price }} <span class="text-xs font-normal text-gray-400">{{ priceTypeLabel(service.price_type) }}</span>
                                </span>
                                <span v-else class="text-xs text-gray-400">{{ priceTypeLabel(service.price_type) }}</span>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="service.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                                >
                                    {{ service.is_active ? 'Aktīvs' : 'Neaktīvs' }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Reviews received -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-navy px-6 py-4">
                    <h2 class="text-sm font-bold text-white">
                        Saņemtās atsauksmes ({{ master.reviews_received.length }})
                    </h2>
                </div>

                <div v-if="master.reviews_received.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                    Nav atsauksmju.
                </div>

                <ul v-else class="divide-y divide-gray-50">
                    <li
                        v-for="review in master.reviews_received"
                        :key="review.id"
                        class="px-6 py-4 flex items-start gap-4 hover:bg-gray-50/60 transition-colors"
                    >
                        <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-600 shrink-0">
                            {{ review.reviewer.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ review.reviewer.name }}</p>
                                <div class="flex items-center gap-0.5 shrink-0">
                                    <StarIcon
                                        v-for="n in 5"
                                        :key="n"
                                        class="w-3.5 h-3.5"
                                        :class="n <= review.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200'"
                                    />
                                </div>
                            </div>
                            <p v-if="review.comment" class="text-sm text-gray-600 mt-1">{{ review.comment }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDate(review.created_at) }}</p>
                        </div>
                    </li>
                </ul>
            </div>

        </div>
    </AdminLayout>
</template>
