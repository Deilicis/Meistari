<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { formatCurrency } from '@/utils/formatters';
import Modal from '@/Components/Common/Modal.vue';
import ImageLightbox from '@/Components/Common/ImageLightbox.vue';
import ComplaintModal from '@/Components/Common/ComplaintModal.vue';
import {
    XMarkIcon,
    MapPinIcon,
    CurrencyEuroIcon,
    CheckBadgeIcon,
    BriefcaseIcon,
    PhotoIcon,
    FlagIcon,
} from '@heroicons/vue/24/outline';
import type { ServiceWithMaster } from '@/types/models';

const { t } = useI18n();

const props = defineProps<{
    show: boolean;
    service: ServiceWithMaster | null;
    applied: boolean;
}>();

const emit = defineEmits<{
    close: [];
    apply: [];
}>();

const profile = computed(() => props.service?.user?.profile ?? null);

const masterName = computed(() => {
    if (!profile.value) return props.service?.user?.name ?? '-';
    if (profile.value.type === 'company') return profile.value.company_name ?? props.service!.user.name;
    const parts = [profile.value.first_name, profile.value.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : props.service!.user.name;
});

const avatarInitials = computed(() => masterName.value.slice(0, 2).toUpperCase());

const formatPrice = (): string => {
    if (!props.service) return '-';
    if (!props.service.price) return t('services.price_negotiable');
    return formatCurrency(props.service.price);
};

const lightboxIndex = ref<number | null>(null);
const complaintOpen = ref(false);
const authUserId = usePage().props.auth?.user?.id as number | undefined;

watch(() => props.show, (newVal) => {
    if (!newVal) {
        lightboxIndex.value = null;
        complaintOpen.value = false;
    }
});
</script>

<template>
    <Modal :show="show" @close="emit('close')" maxWidth="3xl">
        <div v-if="service" class="flex flex-col max-h-[90vh]">

            
            <div class="bg-navy px-6 py-4 flex items-start justify-between gap-4 flex-shrink-0">
                <div class="min-w-0">
                    <span v-if="service.category" class="inline-flex text-xs font-bold text-gold tracking-widest uppercase mb-1">
                        {{ service.category.name }}
                    </span>
                    <h2 class="text-lg font-bold text-white leading-snug">{{ service.title }}</h2>
                </div>
                <button @click="emit('close')" class="text-white/60 hover:text-white transition-colors flex-shrink-0 mt-0.5">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            
            <div class="overflow-y-auto flex-grow">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-0">

                    
                    <div class="md:col-span-2 p-6 border-r border-gray-100">

                        
                        <div class="flex items-center gap-3 mb-5 pb-5 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-xl bg-gold/10 flex items-center justify-center flex-shrink-0">
                                <CurrencyEuroIcon class="w-5 h-5 text-gold" />
                            </div>
                            <div>
                                <p class="text-xl font-extrabold text-navy">{{ formatPrice() }}</p>
                            </div>
                        </div>

                        
                        <div class="mb-5">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">{{ t('services.detail_description') }}</h3>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ service.description }}</p>
                        </div>

                        
                        <div v-if="service.location?.length">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">{{ t('services.detail_locations') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="loc in service.location"
                                    :key="loc"
                                    class="inline-flex items-center gap-1.5 text-sm text-gray-600 bg-gray-50 border border-gray-100 rounded-lg px-3 py-1.5"
                                >
                                    <MapPinIcon class="w-3.5 h-3.5 text-gray-400" />
                                    {{ loc }}
                                </span>
                            </div>
                        </div>
                    </div>

                    
                    <div class="md:col-span-1 p-6 bg-gray-50/60">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">{{ t('services.detail_about_master') }}</h3>

                        
                        <a :href="route('master.public-profile', service.user.id)" class="flex items-center gap-3 mb-4 hover:opacity-80 transition-opacity">
                            <div class="w-12 h-12 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden">
                                <img
                                    v-if="profile?.avatar"
                                    :src="`/storage/${profile.avatar}`"
                                    :alt="masterName"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>{{ avatarInitials }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ masterName }}</p>
                                    <CheckBadgeIcon v-if="profile?.is_verified" class="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                </div>
                                <p v-if="profile?.city" class="text-xs text-gray-500">{{ profile.city }}</p>
                            </div>
                        </a>

                        
                        <p v-if="profile?.bio" class="text-sm text-gray-600 leading-relaxed mb-4 line-clamp-4">
                            {{ profile.bio }}
                        </p>

                        
                        <div v-if="profile?.experiences?.length" class="mb-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <BriefcaseIcon class="w-3.5 h-3.5" />
                                {{ t('services.detail_experience') }}
                            </h4>
                            <ul class="space-y-2">
                                <li
                                    v-for="(exp, i) in profile.experiences.slice(0, 3)"
                                    :key="i"
                                    class="text-sm"
                                >
                                    <p class="font-semibold text-gray-800">{{ (exp as any).title }}</p>
                                    <p class="text-xs text-gray-500">{{ (exp as any).company }}</p>
                                </li>
                            </ul>
                        </div>

                        
                        <div v-if="profile?.portfolio_images?.length" class="mb-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <PhotoIcon class="w-3.5 h-3.5" />
                                {{ t('services.detail_portfolio') }}
                            </h4>
                            <div class="grid grid-cols-4 gap-1.5">
                                <div
                                    v-for="(img, i) in profile.portfolio_images"
                                    :key="img"
                                    class="aspect-square rounded-lg overflow-hidden bg-gray-100 cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="lightboxIndex = i"
                                >
                                    <img :src="`/storage/${img}`" class="w-full h-full object-cover" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-3 flex-shrink-0 bg-white">
                <button
                    v-if="authUserId !== service.user?.id"
                    @click="complaintOpen = true"
                    class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded px-2 py-1 transition-colors"
                    :title="t('modals.complaint.title')"
                >
                    <FlagIcon class="w-3.5 h-3.5" />
                    {{ t('services.detail_report') }}
                </button>
                <div v-else />
                <div class="flex items-center gap-3">
                <button
                    @click="emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    {{ t('services.detail_close') }}
                </button>

                <div
                    v-if="applied"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold"
                >
                    <CheckBadgeIcon class="w-4 h-4" />
                    {{ t('services.detail_applied') }}
                </div>
                <button
                    v-else
                    @click="emit('apply')"
                    class="px-5 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy-hover transition-colors"
                >
                    {{ t('services.detail_apply_btn') }}
                </button>
                </div>
            </div>
        </div>

        <ComplaintModal
            v-if="service"
            :show="complaintOpen"
            :reported-user-id="service.user.id"
            reported-entity-type="App\Models\Service"
            :reported-entity-id="service.id"
            :entity-label="t('services.detail_this_service')"
            @close="complaintOpen = false"
        />

        <ImageLightbox
            v-if="profile?.portfolio_images?.length"
            :images="profile.portfolio_images"
            :index="lightboxIndex"
            @close="lightboxIndex = null"
        />
    </Modal>
</template>
