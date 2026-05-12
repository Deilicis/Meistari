<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps<{ show: boolean; jobId: number }>();
const emit  = defineEmits<{ close: []; submitted: [reason: string] }>();

const reason  = ref('');
const error   = ref('');
const { t } = useI18n();

function submit() {
    if (reason.value.trim().length < 20) {
        error.value = t('modals.dispute.min_chars_error');
        return;
    }
    emit('submitted', reason.value.trim());
    reason.value = '';
    error.value  = '';
}

function close() {
    reason.value = '';
    error.value  = '';
    emit('close');
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="close" />
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-base font-bold text-navy mb-1">{{ t('modals.dispute.title') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ t('modals.dispute.desc') }}</p>

                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ t('modals.dispute.reason_label') }}</label>
                <textarea
                    v-model="reason"
                    rows="4"
                    :placeholder="t('modals.dispute.reason_placeholder')"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none resize-none"
                />
                <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="close" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">{{ t('common.cancel') }}</button>
                    <button @click="submit" class="px-4 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                        {{ t('modals.dispute.submit') }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
