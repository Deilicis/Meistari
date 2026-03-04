<script setup>
import DangerButton from '@/Components/Form/DangerButton.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import Modal from '@/Components/Common/Modal.vue';
import SecondaryButton from '@/Components/Form/SecondaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Dzēst kontu
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Kad jūsu konts tiek dzēsts, visi tā resursi un dati tiks neatgriezeniski dzēsti. Pirms konta dzēšanas, lūdzu, lejupielādējiet jebkuru datu vai informāciju, kuru vēlaties saglabāt.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Dzēst kontu</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Vai esat pārliecināts, ka vēlaties neatgriezeniski dzēst savu kontu?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Kad jūsu konts tiek dzēsts, visi tā resursi un dati tiks neatgriezeniski dzēsti. Lūdzu, ievadiet savu paroli, lai apstiprinātu, ka vēlaties neatgriezeniski dzēst savu kontu.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Parole"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Parole"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Atcelt
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Dzēst kontu
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
