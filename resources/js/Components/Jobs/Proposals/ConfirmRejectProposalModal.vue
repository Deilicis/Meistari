<script setup lang="ts">
import Modal from '@/Components/Common/Modal.vue';

defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();
</script>

<template>
    <Modal :show="show" @close="emit('close')" maxWidth="sm">
        <div class="p-6">
            <h3 class="text-base font-bold text-navy mb-3">Noraidīt piedāvājumu</h3>
            <p class="text-sm text-gray-600 mb-5">
                Pēc noraidījuma šis piedāvājums vairs nebūs spēkā, bet jūs varat iesniegt jaunu piedāvājumu.
            </p>
            <div class="flex justify-end gap-3">
                <button
                    @click="emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Atcelt
                </button>
                <button
                    @click="emit('confirm')"
                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
                >
                    Noraidīt
                </button>
            </div>
        </div>
    </Modal>
</template>
