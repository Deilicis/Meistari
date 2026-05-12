<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Modal from '@/Components/Common/Modal.vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const { t } = useI18n();

defineProps<{
    show: boolean;
    title: string;
    message: string;
    confirmLabel?: string;
    processing?: boolean;
}>();

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <Modal :show="show" @close="emit('cancel')" maxWidth="sm">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                </div>
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ message }}</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <button
                    type="button"
                    @click="emit('cancel')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    {{ t('common.back') }}
                </button>
                <button
                    type="button"
                    @click="emit('confirm')"
                    :disabled="processing"
                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 flex items-center gap-2"
                >
                    <svg v-if="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {{ confirmLabel ?? t('common.confirm') }}
                </button>
            </div>
        </div>
    </Modal>
</template>
