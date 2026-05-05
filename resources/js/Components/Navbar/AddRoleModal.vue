<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import Modal from '@/Components/Common/Modal.vue';
import { UserPlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    show: boolean;
    roleToAdd: 'master' | 'seeker';
}>();

const emit = defineEmits<{ close: [] }>();

const submitting = ref(false);

const roleLabel = props.roleToAdd === 'master' ? 'Meistara' : 'Meklētāja';
const roleDescription = props.roleToAdd === 'master'
    ? 'Kā meistars tu varēsi publicēt savus pakalpojumus, pieņemt darba pieteikumus un pārvaldīt projektus.'
    : 'Kā meklētājs tu varēsi meklēt meistarus, publicēt darba sludinājumus un pieņemt piedāvājumus.';

function confirm() {
    if (submitting.value) return;
    submitting.value = true;

    const routeName = props.roleToAdd === 'master' ? 'role.add-master' : 'role.add-seeker';

    router.post(route(routeName), {}, {
        onFinish:  () => { submitting.value = false; },
        onSuccess: () => {
            emit('close');
            toast.success(`${roleLabel} loma veiksmīgi pievienota!`);
        },
        onError: () => toast.error('Kļūda pievienojot lomu. Lūdzu, mēģini vēlreiz.'),
    });
}
</script>

<template>
    <Modal :show="show" @close="emit('close')" maxWidth="md">
        <div class="bg-navy px-6 py-4 flex items-center gap-3">
            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                <UserPlusIcon class="w-4 h-4 text-white" aria-hidden="true" />
            </div>
            <h2 class="text-base font-bold text-white">Pievienot {{ roleLabel }} lomu</h2>
        </div>

        <div class="p-6">
            <p class="text-sm text-gray-600 leading-relaxed">{{ roleDescription }}</p>

            <div
                class="mt-4 p-3 rounded-lg text-sm"
                :class="roleToAdd === 'master' ? 'bg-yellow-50 text-yellow-800 border border-yellow-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'"
            >
                <strong>Ņem vērā:</strong> pēc lomas pievienošanas varēsi jebkurā laikā pārslēgties starp abām lomām profila izvēlnē.
            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <button
                    type="button"
                    @click="emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Atcelt
                </button>
                <button
                    type="button"
                    @click="confirm"
                    :disabled="submitting"
                    class="px-5 py-2 text-sm font-semibold text-navy rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
                    :class="roleToAdd === 'master' ? 'bg-yellow-400 hover:bg-yellow-300' : 'bg-emerald-400 text-white hover:bg-emerald-500'"
                >
                    <svg v-if="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Pievienot {{ roleLabel }} lomu
                </button>
            </div>
        </div>
    </Modal>
</template>
