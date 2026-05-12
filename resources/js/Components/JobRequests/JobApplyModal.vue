<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import Modal from '@/Components/Common/Modal.vue';
import InputError from '@/Components/Form/InputError.vue';
import { XMarkIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';
import type { JobRequestWithSeeker } from '@/types/models';

const { t } = useI18n();

const props = defineProps<{
    show: boolean;
    job: JobRequestWithSeeker | null;
}>();

const emit = defineEmits<{
    close: [];
    applied: [];
}>();

const processing = ref(false);
const coverLetter = ref('');
const priceOffer = ref('');
const errors = ref<{ cover_letter?: string; price_offer?: string; job_request_id?: string }>({});

const reset = () => {
    coverLetter.value = '';
    priceOffer.value = '';
    errors.value = {};
};

const close = () => {
    reset();
    emit('close');
};

const submit = async () => {
    if (!props.job) return;
    processing.value = true;
    errors.value = {};

    try {
        await axios.post(route('api.applications.store'), {
            job_request_id: props.job.id,
            cover_letter: coverLetter.value || null,
            price_offer: priceOffer.value || null,
        });

        toast.success(t('job_requests.apply_success_toast'));
        reset();
        emit('applied');
    } catch (error: any) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors ?? {};
            toast.error(error.response.data.message ?? t('job_requests.apply_form_error'));
        } else if (error.response?.status === 403) {
            toast.error(error.response.data.message ?? t('job_requests.apply_not_allowed'));
        } else {
            toast.error(t('job_requests.apply_unexpected_error'));
        }
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Modal :show="show" @close="close" max-width="xl">
        <div class="bg-navy px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-400/20 rounded-lg flex items-center justify-center">
                    <ClipboardDocumentListIcon class="w-4 h-4 text-emerald-300" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">{{ t('job_requests.apply_title') }}</h2>
                    <p v-if="job" class="text-xs text-white/50 truncate max-w-xs">{{ job.title }}</p>
                </div>
            </div>
            <button @click="close" type="button" class="text-white/60 hover:text-white transition-colors">
                <XMarkIcon class="w-5 h-5" />
            </button>
        </div>

        <div class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    {{ t('job_requests.apply_price_label') }}
                    <span class="text-gray-400 font-normal">{{ t('job_requests.apply_price_optional') }}</span>
                </label>
                <input
                    v-model="priceOffer"
                    type="number"
                    step="0.01"
                    min="0"
                    class="block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                    :placeholder="t('job_requests.apply_price_placeholder')"
                />
                <InputError v-if="errors.price_offer" :message="errors.price_offer" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    {{ t('job_requests.apply_cover_label') }}
                    <span class="text-gray-400 font-normal">{{ t('job_requests.apply_price_optional') }}</span>
                </label>
                <textarea
                    v-model="coverLetter"
                    rows="5"
                    maxlength="2000"
                    class="block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                    :placeholder="t('job_requests.apply_cover_placeholder')"
                ></textarea>
                <p class="mt-1 text-xs text-gray-400">{{ t('job_requests.apply_cover_max') }}</p>
                <InputError v-if="errors.cover_letter" :message="errors.cover_letter" class="mt-1" />
            </div>

            <InputError v-if="errors.job_request_id" :message="errors.job_request_id" />
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
                {{ processing ? t('job_requests.apply_submitting') : t('job_requests.apply_submit') }}
            </button>
        </div>
    </Modal>
</template>
