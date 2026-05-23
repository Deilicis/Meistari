<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useActiveRole } from '@/composables/useActiveRole';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import type { AuthUser, Profile } from '@/types/models';

const { t } = useI18n();
const { accentBgClass } = useActiveRole();

type ProfileUser = AuthUser & {
    profile: Profile & {
        experiences: object[];
        portfolio_images: string[];
        reg_number?: string;
        vat_number?: string;
    };
};

const page = usePage<{ auth: { user: ProfileUser } }>();
const user = computed(() => page.props.auth.user);
const profile = computed(() => user.value.profile || {});

const props = defineProps<{
    form: any;
}>();

const emit = defineEmits<{
    submit: [];
}>();

// Avatar priekšskatījums — sinhronizēts ar servera avatara ceļu.
const avatarPreview = ref<string | null>(
    profile.value.avatar ? `/storage/${profile.value.avatar}` : null
);

watch(
    () => profile.value.avatar,
    (newAvatar) => {
        avatarPreview.value = newAvatar ? `/storage/${newAvatar}` : null;
    }
);

function handleAvatarChange(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;
    const file = input.files[0];
    props.form.avatar = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
        avatarPreview.value = ev.target?.result as string;
    };
    reader.readAsDataURL(file);
}

function removeAvatar() {
    props.form.avatar = 'delete';
    avatarPreview.value = null;
}
</script>

<template>
    <div
        role="tabpanel"
        id="tabpanel-basic"
        aria-labelledby="tab-basic"
        class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden"
    >
        <form @submit.prevent="emit('submit')" class="divide-y divide-gray-100">

            <!-- Profila bilde -->
            <div class="p-6 space-y-3">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    {{ t('profile.sections.avatar') }}
                </h3>
                <div class="flex items-center gap-6">
                    <div class="shrink-0">
                        <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            :alt="t('common.avatar_alt', { name: user.name })"
                            class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md"
                        />
                        <div
                            v-else
                            class="w-24 h-24 rounded-full flex items-center justify-center border-4 border-white shadow-md text-2xl font-bold"
                            :class="accentBgClass"
                            :aria-label="t('profile.avatar.placeholder_alt', { name: user.name })"
                        >
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>
                    <div class="space-y-2 min-w-0">
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-navy hover:bg-navy-hover text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            {{ t('profile.avatar.change_button') }}
                            <input
                                type="file"
                                class="sr-only"
                                accept="image/*"
                                @change="handleAvatarChange"
                            />
                        </label>
                        <button
                            v-if="avatarPreview"
                            type="button"
                            @click="removeAvatar"
                            class="block text-xs text-red-500 hover:text-red-700 transition-colors"
                        >
                            {{ t('forms.avatar.remove') }}
                        </button>
                        <p class="text-xs text-gray-400">{{ t('profile.avatar.helper') }}</p>
                        <InputError :message="form.errors.avatar as string" />
                    </div>
                </div>
            </div>

            <!-- Pamata dati -->
            <div class="p-6 space-y-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    {{ t('profile.sections.basic_data') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="name" :value="t('profile.field_username_label')" class="font-medium" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.name"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="email" :value="t('common.email')" class="font-medium" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.email"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                </div>
            </div>

            <!-- Personiska informācija -->
            <div class="p-6 space-y-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    {{ t('profile.sections.personal_info') }}
                </h3>

                <div>
                    <InputLabel :value="t('profile.field_type_label')" class="font-medium mb-2" />
                    <div class="flex items-center gap-6">
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="radio"
                                value="individual"
                                v-model="form.type"
                                class="text-navy focus:ring-navy border-gray-300 w-4 h-4 transition-colors"
                            />
                            <span class="ml-2 text-sm text-gray-700">{{ t('profile.type_individual') }}</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="radio"
                                value="company"
                                v-model="form.type"
                                class="text-navy focus:ring-navy border-gray-300 w-4 h-4 transition-colors"
                            />
                            <span class="ml-2 text-sm text-gray-700">{{ t('profile.type_company') }}</span>
                        </label>
                    </div>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <div v-if="form.type === 'individual'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="first_name" :value="t('auth.register.first_name')" class="font-medium" />
                        <TextInput
                            id="first_name"
                            type="text"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.first_name"
                        />
                        <InputError class="mt-2" :message="form.errors.first_name" />
                    </div>
                    <div>
                        <InputLabel for="last_name" :value="t('auth.register.last_name')" class="font-medium" />
                        <TextInput
                            id="last_name"
                            type="text"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.last_name"
                        />
                        <InputError class="mt-2" :message="form.errors.last_name" />
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="company_name" :value="t('auth.register.company_name')" class="font-medium" />
                        <TextInput
                            id="company_name"
                            type="text"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.company_name"
                        />
                        <InputError class="mt-2" :message="form.errors.company_name" />
                    </div>
                    <div>
                        <InputLabel for="reg_number" :value="t('profile.field_reg_number')" class="font-medium" />
                        <TextInput
                            id="reg_number"
                            type="text"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.reg_number"
                        />
                        <InputError class="mt-2" :message="form.errors.reg_number" />
                    </div>
                    <div class="md:col-span-2">
                        <InputLabel for="vat_number" :value="t('profile.field_vat_number')" class="font-medium" />
                        <TextInput
                            id="vat_number"
                            type="text"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.vat_number"
                        />
                        <InputError class="mt-2" :message="form.errors.vat_number" />
                    </div>
                </div>
            </div>

            <!-- Kontakti -->
            <div class="p-6 space-y-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    {{ t('profile.sections.contact') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="city" :value="t('profile.field_city')" class="font-medium" />
                        <TextInput
                            id="city"
                            type="text"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.city"
                            :placeholder="t('profile.city_placeholder')"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.city" />
                    </div>
                    <div>
                        <InputLabel for="phone_number" :value="t('profile.field_phone')" class="font-medium" />
                        <TextInput
                            id="phone_number"
                            type="text"
                            class="mt-1 block w-full bg-gray-50 focus:bg-white"
                            v-model="form.phone_number"
                            placeholder="+371 20000000"
                        />
                        <InputError class="mt-2" :message="form.errors.phone_number" />
                    </div>
                </div>
            </div>

            <!-- Save bar -->
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-between gap-4">
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                    mode="out-in"
                >
                    <div v-if="form.recentlySuccessful" class="flex items-center text-emerald-600">
                        <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium">{{ t('profile.saved_message') }}</span>
                    </div>
                    <p v-else class="text-sm text-gray-400">{{ t('profile.unsaved_message') }}</p>
                </Transition>

                <PrimaryButton
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-navy hover:bg-navy-hover shrink-0"
                >
                    <span v-if="form.processing" class="flex items-center gap-1.5">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ t('profile.saving_btn') }}
                    </span>
                    <span v-else>{{ t('profile.save_changes_btn') }}</span>
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
