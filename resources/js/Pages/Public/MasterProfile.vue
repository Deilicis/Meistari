<script setup lang="ts">
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ImageLightbox from '@/Components/Common/ImageLightbox.vue';
import ComplaintModal from '@/Components/Common/ComplaintModal.vue';
import {
    MapPinIcon,
    CheckBadgeIcon,
    BriefcaseIcon,
    PhotoIcon,
    StarIcon,
    FlagIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';
import type { MasterPublicReview, ServiceMasterProfile } from '@/types/models';

const props = defineProps<{
    master: {
        id: number;
        name: string;
        profile: ServiceMasterProfile | null;
    };
    reviews: MasterPublicReview[];
    avg_rating: number | null;
    review_count: number;
}>();

const profile = props.master.profile;

const displayName = (() => {
    if (!profile) return props.master.name;
    if (profile.type === 'company') return profile.company_name ?? props.master.name;
    const parts = [profile.first_name, profile.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : props.master.name;
})();

const initials = displayName.slice(0, 2).toUpperCase();

const reviewerName = (review: MasterPublicReview): string => {
    const p = review.reviewer.profile;
    if (!p) return review.reviewer.name;
    if (p.type === 'company') return p.company_name ?? review.reviewer.name;
    const parts = [p.first_name, p.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : review.reviewer.name;
};

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('lv-LV', { year: 'numeric', month: 'short', day: 'numeric' });

const lightboxIndex = ref<number | null>(null);
const complaintOpen = ref(false);
const authUserId = usePage().props.auth?.user?.id as number | undefined;
</script>

<template>
    <Head :title="displayName" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Profile header -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-start gap-5">
                        <div class="w-20 h-20 rounded-2xl bg-navy flex items-center justify-center text-white font-bold text-xl flex-shrink-0 overflow-hidden">
                            <img
                                v-if="profile?.avatar"
                                :src="`/storage/${profile.avatar}`"
                                :alt="displayName"
                                class="w-full h-full object-cover"
                            />
                            <span v-else>{{ initials }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h1 class="text-2xl font-bold text-navy">{{ displayName }}</h1>
                                <CheckBadgeIcon v-if="profile?.is_verified" class="w-5 h-5 text-emerald-500 flex-shrink-0" />
                                <button
                                    v-if="authUserId !== master.id"
                                    @click="complaintOpen = true"
                                    class="ml-auto inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded px-2 py-1 transition-colors"
                                    title="Ziņot par pārkāpumu"
                                >
                                    <FlagIcon class="w-3.5 h-3.5" />
                                    Ziņot
                                </button>
                            </div>

                            <div class="flex items-center gap-3 text-sm text-gray-500 flex-wrap mb-3">
                                <span v-if="profile?.city" class="flex items-center gap-1">
                                    <MapPinIcon class="w-4 h-4 text-gray-400" />
                                    {{ profile.city }}
                                </span>
                                <span v-if="profile?.type" class="text-gray-400">
                                    {{ profile.type === 'company' ? 'Uzņēmums' : 'Privātpersona' }}
                                </span>
                            </div>

                            <!-- Rating summary -->
                            <div v-if="avg_rating !== null" class="flex items-center gap-2">
                                <div class="flex items-center gap-0.5">
                                    <StarIconSolid
                                        v-for="i in 5"
                                        :key="i"
                                        class="w-4 h-4"
                                        :class="i <= Math.round(avg_rating) ? 'text-gold' : 'text-gray-200'"
                                    />
                                </div>
                                <span class="text-sm font-bold text-navy">{{ avg_rating }}</span>
                                <span class="text-sm text-gray-400">({{ review_count }} atsauksm{{ review_count === 1 ? 'e' : 'es' }})</span>
                            </div>

                            <p v-if="profile?.bio" class="text-sm text-gray-600 leading-relaxed mt-3">{{ profile.bio }}</p>
                        </div>
                    </div>
                </div>

                <!-- Experiences -->
                <div v-if="profile?.experiences?.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wide mb-4 flex items-center gap-2">
                        <BriefcaseIcon class="w-4 h-4" />
                        Pieredze
                    </h2>
                    <ul class="space-y-4">
                        <li v-for="(exp, i) in profile.experiences" :key="i" class="border-l-2 border-navy/20 pl-4">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <p class="text-sm font-semibold text-gray-800">{{ (exp as any).title }}</p>
                                <span v-if="(exp as any).years" class="text-xs bg-navy/10 text-navy px-2 py-0.5 rounded-full font-medium">
                                    {{ (exp as any).years }} {{ Number((exp as any).years) === 1 ? 'gads' : 'gadi' }} pieredze
                                </span>
                            </div>
                            <p v-if="(exp as any).description" class="text-xs text-gray-500 leading-relaxed">{{ (exp as any).description }}</p>
                        </li>
                    </ul>
                </div>

                <!-- Portfolio -->
                <div v-if="profile?.portfolio_images?.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wide mb-4 flex items-center gap-2">
                        <PhotoIcon class="w-4 h-4" />
                        Portfolio
                    </h2>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                        <div
                            v-for="(img, i) in profile.portfolio_images"
                            :key="img"
                            class="aspect-square rounded-xl overflow-hidden bg-gray-100 cursor-pointer hover:opacity-90 transition-opacity"
                            @click="lightboxIndex = i"
                        >
                            <img :src="`/storage/${img}`" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wide flex items-center gap-2">
                            <StarIcon class="w-4 h-4" />
                            Atsauksmes
                        </h2>
                        <span v-if="review_count > 0" class="text-sm font-semibold bg-navy/10 text-navy px-2.5 py-0.5 rounded-full">
                            {{ review_count }}
                        </span>
                    </div>

                    <div v-if="reviews.length === 0" class="py-8 text-center text-sm text-gray-400">
                        Vēl nav nevienas atsauksmes.
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="review in reviews"
                            :key="review.id"
                            class="border-b border-gray-100 pb-4 last:border-0 last:pb-0"
                        >
                            <div class="flex items-start gap-3">
                                <a :href="route('seeker.public-profile', review.reviewer.id)" class="w-9 h-9 rounded-full bg-navy/10 flex items-center justify-center text-navy font-bold text-xs flex-shrink-0 overflow-hidden hover:opacity-80 transition-opacity">
                                    <img
                                        v-if="review.reviewer.profile?.avatar"
                                        :src="`/storage/${review.reviewer.profile.avatar}`"
                                        :alt="reviewerName(review)"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else>{{ reviewerName(review).slice(0, 2).toUpperCase() }}</span>
                                </a>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <a
                                            :href="route('seeker.public-profile', review.reviewer.id)"
                                            class="text-sm font-semibold text-gray-800 hover:text-navy hover:underline transition-colors"
                                        >{{ reviewerName(review) }}</a>
                                        <div class="flex items-center gap-0.5">
                                            <StarIconSolid
                                                v-for="i in 5"
                                                :key="i"
                                                class="w-3.5 h-3.5"
                                                :class="i <= review.rating ? 'text-gold' : 'text-gray-200'"
                                            />
                                        </div>
                                        <span class="text-xs text-gray-400">{{ formatDate(review.created_at) }}</span>
                                    </div>
                                    <p v-if="review.comment" class="text-sm text-gray-600 leading-relaxed">{{ review.comment }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <ImageLightbox
            v-if="profile?.portfolio_images?.length"
            :images="profile.portfolio_images"
            :index="lightboxIndex"
            @close="lightboxIndex = null"
        />

        <ComplaintModal
            :show="complaintOpen"
            :reported-user-id="master.id"
            @close="complaintOpen = false"
        />
    </AuthenticatedLayout>
</template>
