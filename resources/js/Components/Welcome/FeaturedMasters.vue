<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { CheckBadgeIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';
import type { FeaturedMaster } from '@/types/welcome';

const { t } = useI18n();

defineProps<{
    masters: FeaturedMaster[];
    isAuth: boolean;
    canRegister?: boolean;
}>();

const displayName = (master: FeaturedMaster): string => {
    const p = master.profile;
    if (!p) return master.name;
    if (p.type === 'company') return p.company_name ?? master.name;
    const parts = [p.first_name, p.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : master.name;
};

const initials = (master: FeaturedMaster): string =>
    displayName(master).slice(0, 2).toUpperCase();

const formatRating = (r: number | null): string =>
    r !== null ? Number(r).toFixed(1) : '-';

const isTopRated = (master: FeaturedMaster): boolean =>
    master.reviews_received_avg_rating !== null &&
    master.reviews_received_avg_rating >= 4.8 &&
    master.reviews_received_count > 0;
</script>

<template>
    <section v-if="masters.length > 0" class="py-16 bg-white border-t border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-navy">{{ t('welcome.featured_masters.title') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ t('welcome.featured_masters.subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="master in masters"
                    :key="master.id"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-gray-200 transition-all duration-200 flex flex-col overflow-hidden relative"
                >
                    <div class="h-1.5 bg-gold" />

                    <span
                        v-if="isTopRated(master)"
                        class="absolute top-4 right-4 text-[10px] font-bold text-gold bg-gold/10 border border-gold/20 px-2 py-0.5 rounded-full"
                    >
                        {{ t('welcome.featured_masters.top_rated') }}
                    </span>

                    <div class="p-7 flex flex-col flex-grow">

                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-[72px] h-[72px] rounded-full bg-navy flex items-center justify-center text-white font-bold text-lg flex-shrink-0 overflow-hidden">
                                <img
                                    v-if="master.profile?.avatar"
                                    :src="`/storage/${master.profile.avatar}`"
                                    :alt="displayName(master)"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>{{ initials(master) }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <p class="text-base font-bold text-gray-900 truncate">{{ displayName(master) }}</p>
                                    <CheckBadgeIcon
                                        v-if="master.profile?.is_verified"
                                        class="w-5 h-5 text-emerald-500 flex-shrink-0"
                                        aria-label="Verificēts"
                                    />
                                </div>
                                <p v-if="master.profile?.city" class="text-sm text-gray-400 flex items-center gap-0.5 mt-0.5">
                                    <MapPinIcon class="w-3.5 h-3.5 inline flex-shrink-0" aria-hidden="true" />
                                    {{ master.profile.city }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 mb-4">
                            <div class="flex items-center gap-0.5">
                                <StarIcon
                                    v-for="i in 5"
                                    :key="i"
                                    class="w-4 h-4"
                                    :class="master.reviews_received_avg_rating && i <= Math.round(master.reviews_received_avg_rating)
                                        ? 'text-gold'
                                        : 'text-gray-200'"
                                    aria-hidden="true"
                                />
                            </div>
                            <span class="text-sm font-semibold text-navy">{{ formatRating(master.reviews_received_avg_rating) }}</span>
                            <span class="text-sm text-gray-400">({{ master.reviews_received_count }})</span>
                        </div>

                        <p
                            v-if="master.profile?.bio"
                            class="text-sm text-gray-500 leading-relaxed line-clamp-4 mb-5 flex-grow"
                        >
                            {{ master.profile.bio }}
                        </p>
                        <div v-else class="flex-grow" />

                        <Link
                            v-if="isAuth"
                            :href="route('master.public-profile', master.id)"
                            class="inline-flex items-center justify-center w-full py-3 text-sm font-bold text-navy border-2 border-gold/30 hover:border-gold/60 hover:bg-gold/5 rounded-xl transition-all duration-200"
                        >
                            {{ t('welcome.featured_masters.view_profile') }}
                        </Link>
                        <Link
                            v-else-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center justify-center w-full py-3 text-sm font-bold text-navy border-2 border-gold/30 hover:border-gold/60 hover:bg-gold/5 rounded-xl transition-all duration-200"
                        >
                            {{ t('welcome.featured_masters.view_profile') }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
