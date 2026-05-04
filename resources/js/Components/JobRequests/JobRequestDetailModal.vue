<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import ImageLightbox from '@/Components/Common/ImageLightbox.vue';
import ComplaintModal from '@/Components/Common/ComplaintModal.vue';
import {
    XMarkIcon,
    MapPinIcon,
    CurrencyEuroIcon,
    CalendarDaysIcon,
    CheckBadgeIcon,
    PhotoIcon,
    FlagIcon,
} from '@heroicons/vue/24/outline';
import type { JobRequestWithSeeker } from '@/types/models';

const props = defineProps<{
    show: boolean;
    job: JobRequestWithSeeker | null;
    applied: boolean;
}>();

const emit = defineEmits<{
    close: [];
    apply: [];
}>();

const profile = computed(() => props.job?.user?.profile ?? null);

const seekerName = computed(() => {
    if (!profile.value) return props.job?.user?.name ?? '—';
    if (profile.value.type === 'company') return profile.value.company_name ?? props.job!.user.name;
    const parts = [profile.value.first_name, profile.value.last_name].filter(Boolean);
    return parts.length ? parts.join(' ') : props.job!.user.name;
});

const avatarInitials = computed(() => seekerName.value.slice(0, 2).toUpperCase());

const formatDeadline = (d: string) =>
    new Date(d).toLocaleString('lv-LV', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });

const lightboxIndex = ref<number | null>(null);
const complaintOpen = ref(false);
const authUserId = usePage().props.auth?.user?.id as number | undefined;

watch(() => props.show, (newVal) => {
    if (!newVal) {
        lightboxIndex.value = null;
        complaintOpen.value = false;
    }
});

const formatBudget = (): string => {
    if (!props.job?.budget) return 'Vienojams';
    return new Intl.NumberFormat('lv-LV', {
        style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
    }).format(props.job.budget);
};
</script>

<template>
    <Modal :show="show" @close="emit('close')" maxWidth="3xl">
        <div v-if="job" class="flex flex-col max-h-[90vh]">

            <div class="bg-navy px-6 py-4 flex items-start justify-between gap-4 flex-shrink-0">
                <div class="min-w-0">
                    <span v-if="job.category" class="inline-flex text-xs font-bold text-gold tracking-widest uppercase mb-1">
                        {{ job.category.name }}
                    </span>
                    <h2 class="text-lg font-bold text-white leading-snug">{{ job.title }}</h2>
                </div>
                <button @click="emit('close')" class="text-white/60 hover:text-white transition-colors flex-shrink-0 mt-0.5">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <div class="overflow-y-auto flex-grow">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-0">

                    <div class="md:col-span-2 p-6 border-r border-gray-100">

                        <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gold/10 flex items-center justify-center flex-shrink-0">
                                    <CurrencyEuroIcon class="w-5 h-5 text-gold" />
                                </div>
                                <div>
                                    <p class="text-xl font-extrabold text-navy">{{ formatBudget() }}</p>
                                    <p class="text-xs text-gray-400">Budžets</p>
                                </div>
                            </div>
                            <div v-if="job.deadline" class="flex items-center gap-2 text-sm text-gray-600">
                                <CalendarDaysIcon class="w-4 h-4 text-gray-400" />
                                <span>{{ formatDeadline(job.deadline) }}</span>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Apraksts</h3>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ job.description }}</p>
                        </div>

                        <div v-if="job.location?.length">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Darba vieta</h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="loc in job.location"
                                    :key="loc"
                                    class="inline-flex items-center gap-1.5 text-sm text-gray-600 bg-gray-50 border border-gray-100 rounded-lg px-3 py-1.5"
                                >
                                    <MapPinIcon class="w-3.5 h-3.5 text-gray-400" />
                                    {{ loc }}
                                </span>
                            </div>
                        </div>

                        <div v-if="job.images?.length" class="mt-5">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <PhotoIcon class="w-3.5 h-3.5" />
                                Attēli
                            </h3>
                            <div class="grid grid-cols-4 gap-1.5">
                                <div
                                    v-for="(img, i) in job.images"
                                    :key="img"
                                    class="aspect-square rounded-lg overflow-hidden bg-gray-100 cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="lightboxIndex = i"
                                >
                                    <img :src="`/storage/${img}`" class="w-full h-full object-cover" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-1 p-6 bg-gray-50/60">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Par pasūtītāju</h3>

                        <a
                            v-if="job?.user?.id"
                            :href="route('seeker.public-profile', job.user.id)"
                            class="flex items-center gap-3 mb-4 hover:opacity-80 transition-opacity"
                        >
                            <div class="w-12 h-12 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden">
                                <img
                                    v-if="profile?.avatar"
                                    :src="`/storage/${profile.avatar}`"
                                    :alt="seekerName"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>{{ avatarInitials }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ seekerName }}</p>
                                <p v-if="profile?.city" class="text-xs text-gray-500">{{ profile.city }}</p>
                                <span
                                    v-if="profile?.type"
                                    class="inline-flex text-xs text-gray-400 mt-0.5"
                                >
                                    {{ profile.type === 'company' ? 'Uzņēmums' : 'Privātpersona' }}
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-3 flex-shrink-0 bg-white">
                <button
                    v-if="authUserId !== job.user?.id"
                    @click="complaintOpen = true"
                    class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded px-2 py-1 transition-colors"
                    title="Ziņot par pārkāpumu"
                >
                    <FlagIcon class="w-3.5 h-3.5" />
                    Ziņot
                </button>
                <div v-else />
                <div class="flex items-center gap-3">
                    <button
                        @click="emit('close')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Aizvērt
                    </button>

                    <div
                        v-if="applied"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold"
                    >
                        <CheckBadgeIcon class="w-4 h-4" />
                        Jau pieteicies
                    </div>
                    <button
                        v-else
                        @click="emit('apply')"
                        class="px-5 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy-hover transition-colors"
                    >
                        Pieteikties
                    </button>
                </div>
            </div>
        </div>

        <ComplaintModal
            v-if="job"
            :show="complaintOpen"
            :reported-user-id="job.user.id"
            reported-entity-type="App\Models\JobRequest"
            :reported-entity-id="job.id"
            entity-label="šo sludinājumu"
            @close="complaintOpen = false"
        />

        <ImageLightbox
            v-if="job?.images?.length"
            :images="job.images"
            :index="lightboxIndex"
            @close="lightboxIndex = null"
        />
    </Modal>
</template>
