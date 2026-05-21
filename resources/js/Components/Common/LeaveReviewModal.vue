<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import Modal from '@/Components/Common/Modal.vue';
import InputError from '@/Components/Form/InputError.vue';
import { XMarkIcon, StarIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

const { t } = useI18n();

const props = defineProps<{
    show: boolean;
    jobRequestId: number | null;
    revieweeId: number | null;
    revieweeName: string;
}>();

const emit = defineEmits<{
    close: [];
    submitted: [];
}>();

const rating = ref(0);
const hovered = ref(0);
const comment = ref('');
const processing = ref(false);
const errors = ref<{ rating?: string; comment?: string }>({});

const reset = () => {
    rating.value = 0;
    hovered.value = 0;
    comment.value = '';
    errors.value = {};
};

const close = () => {
    reset();
    emit('close');
};

const submit = async () => {
    if (!props.jobRequestId || !props.revieweeId) return;
    if (rating.value === 0) {
        errors.value.rating = t('modals.review.rating_error');
        return;
    }
    processing.value = true;
    errors.value = {};

    try {
        await axios.post(route('api.reviews.store'), {
            job_request_id: props.jobRequestId,
            reviewee_id:    props.revieweeId,
            rating:         rating.value,
            comment:        comment.value || null,
        });
        toast.success(t('modals.review.success'));
        reset();
        emit('submitted');
    } catch (e: any) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors ?? {};
            toast.error(e.response.data.message ?? t('modals.review.error'));
        } else {
            toast.error(e.response?.data?.message ?? t('modals.review.error'));
        }
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="md">
        <div class="bg-navy px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-white">{{ t('modals.review.title') }}</h2>
                <p class="text-xs text-white/50 mt-0.5">{{ t('modals.review.for', { name: revieweeName }) }}</p>
            </div>
            <button @click="close" class="text-white/60 hover:text-white transition-colors">
                <XMarkIcon class="w-5 h-5" />
            </button>
        </div>

        <div class="p-6 space-y-5">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('modals.review.rating_label') }}</label>
                <div class="flex items-center gap-1">
                    <button
                        v-for="star in 5"
                        :key="star"
                        type="button"
                        @mouseenter="hovered = star"
                        @mouseleave="hovered = 0"
                        @click="rating = star"
                        class="focus:outline-none transition-transform hover:scale-110"
                    >
                        <StarIconSolid
                            v-if="star <= (hovered || rating)"
                            class="w-8 h-8 text-gold"
                        />
                        <StarIcon
                            v-else
                            class="w-8 h-8 text-gray-300"
                        />
                    </button>
                </div>
                <InputError v-if="errors.rating" :message="errors.rating" class="mt-1" />
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    {{ t('modals.review.comment_label') }}
                    <span class="text-gray-400 font-normal">{{ t('modals.review.comment_optional') }}</span>
                </label>
                <textarea
                    v-model="comment"
                    rows="4"
                    maxlength="1000"
                    class="block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                    :placeholder="t('modals.review.comment_placeholder')"
                ></textarea>
                <InputError v-if="errors.comment" :message="errors.comment" class="mt-1" />
            </div>
        </div>

        <div class="px-6 pb-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
            <button
                type="button"
                @click="close"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                {{ t('common.cancel') }}
            </button>
            <button
                type="button"
                @click="submit"
                :disabled="processing"
                class="px-5 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy-hover transition-colors disabled:opacity-50 flex items-center gap-2"
            >
                <svg v-if="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ processing ? t('modals.review.submitting') : t('modals.review.submit') }}
            </button>
        </div>
    </Modal>
</template>
