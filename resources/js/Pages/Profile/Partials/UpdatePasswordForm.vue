<script setup lang="ts">
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';

const { t } = useI18n();

const passwordInput = ref<InstanceType<typeof TextInput> | null>(null);
const currentPasswordInput = ref<InstanceType<typeof TextInput> | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.$el?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.$el?.focus();
            }
        },
    });
};
</script>

<template>
    <form @submit.prevent="updatePassword" class="space-y-5">
        <div>
            <InputLabel for="current_password" :value="t('profile.current_password_label')" />
            <TextInput
                id="current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="current-password"
            />
            <InputError :message="form.errors.current_password" class="mt-2" />
        </div>

        <div>
            <InputLabel for="password" :value="t('profile.new_password_label')" />
            <TextInput
                id="password"
                ref="passwordInput"
                v-model="form.password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
            />
            <InputError :message="form.errors.password" class="mt-2" />
        </div>

        <div>
            <InputLabel for="password_confirmation" :value="t('profile.confirm_password_label')" />
            <TextInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
            />
            <InputError :message="form.errors.password_confirmation" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-1">
            <PrimaryButton :disabled="form.processing">{{ t('profile.save_password_btn') }}</PrimaryButton>
            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600 font-medium">
                    {{ t('profile.password_saved_msg') }}
                </p>
            </Transition>
        </div>
    </form>
</template>
