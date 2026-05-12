<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import Modal from '@/Components/Common/Modal.vue';
import { FlagIcon } from '@heroicons/vue/24/outline';

const { t } = useI18n();

const props = defineProps<{
    show: boolean;
    reportedUserId: number;
    reportedEntityType?: string;
    reportedEntityId?: number;
    entityLabel?: string;
}>();

const emit = defineEmits<{ close: [] }>();

const reason = ref('');
const error = ref('');
const submitting = ref(false);

const close = () => {
    reason.value = '';
    error.value = '';
    emit('close');
};

const submit = async () => {
    error.value = '';
    if (reason.value.trim().length < 20) {
        error.value = t('modals.complaint.min_chars_error');
        return;
    }
    submitting.value = true;
    try {
        await axios.post(route('api.complaints.store'), {
            reported_user_id:    props.reportedUserId,
            reported_entity_type: props.reportedEntityType ?? null,
            reported_entity_id:   props.reportedEntityId ?? null,
            reason:              reason.value.trim(),
        });
        toast.success(t('modals.complaint.success'));
        close();
    } catch (e: any) {
        const msg = e?.response?.data?.message ?? e?.response?.data?.errors?.reason?.[0];
        error.value = msg ?? t('modals.complaint.error');
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="md">
        <div class="bg-navy px-6 py-4 rounded-t-xl flex items-center gap-3">
            <FlagIcon class="w-5 h-5 text-rose-400 flex-shrink-0" />
            <div>
                <h2 class="text-base font-bold text-white">{{ t('modals.complaint.title') }}</h2>
                <p class="text-white/50 text-xs mt-0.5">
                    {{ entityLabel ? t('modals.complaint.reporting', { label: entityLabel }) : t('modals.complaint.reporting_generic') }}
                </p>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ t('modals.complaint.reason_label') }}</label>
                <textarea
                    v-model="reason"
                    rows="4"
                    :placeholder="t('modals.complaint.reason_placeholder')"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400 resize-none transition"
                />
                <p v-if="error" class="text-xs text-rose-500 mt-1">{{ error }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ t('modals.complaint.min_chars', { count: reason.trim().length }) }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button
                    type="button"
                    @click="close"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    {{ t('common.cancel') }}
                </button>
                <button
                    type="button"
                    @click="submit"
                    :disabled="submitting"
                    class="px-4 py-2 text-sm font-semibold text-white bg-rose-500 rounded-lg hover:bg-rose-600 transition-colors disabled:opacity-50"
                >
                    {{ t('modals.complaint.submit') }}
                </button>
            </div>
        </div>
    </Modal>
</template>
