<script setup lang="ts">
import Modal from '@/Components/Common/Modal.vue';
import SecondaryButton from '@/Components/Form/SecondaryButton.vue';

defineProps<{
    show: boolean;
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    confirmButtonClass?: string;
}>();

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" @close="emit('close')" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-2">
                {{ title }}
            </h2>
            <p class="text-sm text-gray-600 mb-6">
                {{ message }}
            </p>

            <div class="flex justify-end gap-3">
                <SecondaryButton @click="emit('close')">
                    {{ cancelText || 'Atcelt' }}
                </SecondaryButton>
                <button 
                    @click="emit('confirm')" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :class="confirmButtonClass || 'bg-indigo-600 hover:bg-indigo-700'"
                >
                    {{ confirmText || 'Apstiprināt' }}
                </button>
            </div>
        </div>
    </Modal>
</template>