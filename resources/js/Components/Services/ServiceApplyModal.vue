<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import Modal from '@/Components/Common/Modal.vue';
import InputError from '@/Components/Form/InputError.vue';
import { XMarkIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';
import type { ServiceWithMaster } from '@/types/models';

const props = defineProps<{
    show: boolean;
    service: ServiceWithMaster | null;
}>();

const emit = defineEmits<{
    close: [];
    applied: [];
}>();

const processing = ref(false);
const message = ref('');
const budgetOffer = ref('');
const errors = ref<{ message?: string; budget_offer?: string; service_id?: string }>({});

const reset = () => {
    message.value = '';
    budgetOffer.value = '';
    errors.value = {};
};

const close = () => {
    reset();
    emit('close');
};

const submit = async () => {
    if (!props.service) return;
    processing.value = true;
    errors.value = {};

    try {
        await axios.post(route('api.service-applications.store'), {
            service_id: props.service.id,
            message: message.value,
            budget_offer: budgetOffer.value || null,
        });

        toast.success('Pieteikums veiksmīgi iesniegts!');
        reset();
        emit('applied');
    } catch (error: any) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors ?? {};
            toast.error('Lūdzu, pārbaudiet formu.');
        } else {
            toast.error('Notika neparedzēta kļūda.');
        }
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Modal :show="show" @close="close">
        <div class="bg-navy px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-400/20 rounded-lg flex items-center justify-center">
                    <ClipboardDocumentListIcon class="w-4 h-4 text-emerald-300" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Pieteikties</h2>
                    <p v-if="service" class="text-xs text-white/50 truncate max-w-xs">{{ service.title }}</p>
                </div>
            </div>
            <button @click="close" type="button" class="text-white/60 hover:text-white transition-colors">
                <XMarkIcon class="w-5 h-5" />
            </button>
        </div>

        <div class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Jūsu ziņojums <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="message"
                    rows="5"
                    class="block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                    placeholder="Aprakstiet sevi, savu pieredzi un kāpēc esat piemērots šim darbam..."
                ></textarea>
                <p class="mt-1 text-xs text-gray-400">Minimums 10 rakstzīmes</p>
                <InputError v-if="errors.message" :message="errors.message" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Budžeta piedāvājums (€)
                    <span class="text-gray-400 font-normal">(neobligāti)</span>
                </label>
                <input
                    v-model="budgetOffer"
                    type="number"
                    step="0.01"
                    min="0"
                    class="block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                    placeholder="Piem., 150"
                />
                <InputError v-if="errors.budget_offer" :message="errors.budget_offer" class="mt-1" />
            </div>

            <InputError v-if="errors.service_id" :message="errors.service_id" />
        </div>

        <div class="px-6 pb-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
            <button
                type="button"
                @click="close"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                Atcelt
            </button>
            <button
                type="button"
                @click="submit"
                :disabled="processing || message.length < 10"
                class="px-5 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy-hover transition-colors disabled:opacity-50 flex items-center gap-2"
            >
                <svg v-if="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ processing ? 'Iesniedz...' : 'Iesniegt pieteikumu' }}
            </button>
        </div>
    </Modal>
</template>
