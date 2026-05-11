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
    r !== null ? Number(r).toFixed(1) : '—';
</script>

<template>
    <section v-if="masters.length > 0" class="py-16 bg-white border-t border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-navy">{{ t('welcome.featured_masters.title') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ t('welcome.featured_masters.subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div
                    v-for="master in masters"
                    :key="master.id"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-gray-200 transition-all flex flex-col overflow-hidden"
                >
                    <div class="h-1 bg-gold" />

                    <div class="p-5 flex flex-col flex-grow">
                        <!-- Avatar + verified -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden">
                                <img
                                    v-if="master.profile?.avatar"
                                    :src="`/storage/${master.profile.avatar}`"
                                    :alt="displayName(master)"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>{{ initials(master) }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1 flex-wrap">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ displayName(master) }}</p>
                                    <CheckBadgeIcon v-if="master.profile?.is_verified" class="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                </div>
                                <p v-if="master.profile?.city" class="text-xs text-gray-400 flex items-center gap-0.5 mt-0.5">
                                    <MapPinIcon class="w-3 h-3 inline" />
                                    {{ master.profile.city }}
                                </p>
                            </div>
                        </div>

                        <!-- Bio excerpt -->
                        <p v-if="master.profile?.bio" class="text-xs text-gray-500 leading-relaxed line-clamp-2 mb-4 flex-grow">
                            {{ master.profile.bio }}
                        </p>
                        <div v-else class="flex-grow" />

                        <!-- Rating -->
                        <div class="flex items-center gap-1.5 mb-4">
                            <div class="flex items-center gap-0.5">
                                <StarIcon
                                    v-for="i in 5"
                                    :key="i"
                                    class="w-3.5 h-3.5"
                                    :class="master.reviews_received_avg_rating && i <= Math.round(master.reviews_received_avg_rating)
                                        ? 'text-gold'
                                        : 'text-gray-200'"
                                />
                            </div>
                            <span class="text-xs font-semibold text-navy">{{ formatRating(master.reviews_received_avg_rating) }}</span>
                            <span class="text-xs text-gray-400">({{ master.reviews_received_count }})</span>
                        </div>

                        <!-- CTA -->
                        <Link
                            v-if="isAuth"
                            :href="route('master.public-profile', master.id)"
                            class="inline-flex items-center justify-center w-full py-2 text-xs font-bold text-navy bg-gold/10 hover:bg-gold/20 border border-gold/20 rounded-lg transition-colors"
                        >
                            {{ t('welcome.featured_masters.view_profile') }}
                        </Link>
                        <Link
                            v-else-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center justify-center w-full py-2 text-xs font-bold text-navy bg-gold/10 hover:bg-gold/20 border border-gold/20 rounded-lg transition-colors"
                        >
                            {{ t('welcome.featured_masters.view_profile') }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
