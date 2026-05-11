<script setup lang="ts">
import DangerButton from '@/Components/Form/DangerButton.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import Modal from '@/Components/Common/Modal.vue';
import SecondaryButton from '@/Components/Form/SecondaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { nextTick, ref } from 'vue';

const { t } = useI18n();

const confirmingUserDeletion = ref(false);
const passwordInput = ref<InstanceType<typeof TextInput> | null>(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.$el?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.$el?.focus(),
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
    <section class="space-y-4">
        <p class="text-sm text-gray-600">{{ t('profile.delete_account_desc') }}</p>

        <DangerButton @click="confirmUserDeletion">{{ t('profile.delete_account_btn') }}</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900">{{ t('profile.delete_confirm_title') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ t('profile.delete_confirm_desc') }}</p>

                <div class="mt-6">
                    <InputLabel for="password" :value="t('profile.delete_password_label')" class="sr-only" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        :placeholder="t('profile.delete_password_placeholder')"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">{{ t('profile.cancel_btn') }}</SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ t('profile.delete_account_btn') }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
