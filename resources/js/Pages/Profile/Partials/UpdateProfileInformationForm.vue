<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import { toast } from 'vue-sonner';
import AvatarUpload from '@/Components/Form/AvatarUpload.vue';
import ExperienceEditor from '@/Components/Form/ExperienceEditor.vue';
import PortfolioUploader from '@/Components/Form/PortfolioUploader.vue';

import type { AuthUser, Profile } from '@/types/models';

const { t } = useI18n();

type ProfileUser = AuthUser & { profile: Profile & { experiences: object[]; portfolio_images: string[]; reg_number?: string; vat_number?: string } };
const page = usePage<{ auth: { user: ProfileUser } }>();
const user = computed(() => page.props.auth.user);
const profile = computed(() => user.value.profile || {});
const isMaster = computed(() => user.value.roles?.includes('master'));
const existingPortfolio = ref<string[]>(profile.value.portfolio_images || []);

watch(() => profile.value.portfolio_images, (newImages) => {
    existingPortfolio.value = newImages || [];
}, { deep: true });

const form = useForm({
    _method: 'patch',
    name: user.value.name,
    email: user.value.email,
    type: profile.value.type || 'individual',
    first_name: profile.value.first_name || '',
    last_name: profile.value.last_name || '',
    company_name: profile.value.company_name || '',
    reg_number: profile.value.reg_number || '',
    vat_number: profile.value.vat_number || '',
    city: profile.value.city || '',
    phone_number: profile.value.phone || '',
    description: profile.value.bio || '',
    avatar: null as File | string | null,
    experiences: profile.value.experiences || [],
    portfolio_images: [] as File[],
    images_to_delete: [] as string[],
});

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.portfolio_images = [];
            form.images_to_delete = [];
            form.avatar = null;
        },
        onError: () => toast.error(t('profile.form_error_toast')),
    });
};
</script>

<template>
    <section class="max-w-7xl mx-auto p-2">
        <form @submit.prevent="submit" class="space-y-8">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-navy px-6 py-4 border-b border-navy-light">
                    <h3 class="text-base font-semibold text-white">{{ t('profile.heading') }}</h3>
                </div>

                <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="name" :value="t('profile.field_username_label')" class="font-medium" />
                                <TextInput id="name" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.name" required />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>
                            <div>
                                <InputLabel for="email" :value="t('common.email')" class="font-medium" />
                                <TextInput id="email" type="email" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.email" required />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                        </div>

                        <hr class="border-gray-100" />

                        <div>
                            <InputLabel :value="t('profile.field_type_label')" class="font-medium mb-3" />
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" value="individual" v-model="form.type" class="text-navy focus:ring-navy border-gray-300 w-4 h-4 transition-colors" />
                                    <span class="ml-2 text-sm text-gray-700">{{ t('profile.type_individual') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" value="company" v-model="form.type" class="text-navy focus:ring-navy border-gray-300 w-4 h-4 transition-colors" />
                                    <span class="ml-2 text-sm text-gray-700">{{ t('profile.type_company') }}</span>
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.type" />
                        </div>

                        <div v-if="form.type === 'individual'" class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in">
                            <div>
                                <InputLabel for="first_name" :value="t('auth.register.first_name')" class="font-medium" />
                                <TextInput id="first_name" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.first_name" />
                                <InputError class="mt-2" :message="form.errors.first_name" />
                            </div>
                            <div>
                                <InputLabel for="last_name" :value="t('auth.register.last_name')" class="font-medium" />
                                <TextInput id="last_name" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.last_name" />
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in">
                            <div>
                                <InputLabel for="company_name" :value="t('auth.register.company_name')" class="font-medium" />
                                <TextInput id="company_name" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.company_name" />
                                <InputError class="mt-2" :message="form.errors.company_name" />
                            </div>
                            <div>
                                <InputLabel for="reg_number" :value="t('profile.field_reg_number')" class="font-medium" />
                                <TextInput id="reg_number" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.reg_number" />
                                <InputError class="mt-2" :message="form.errors.reg_number" />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="vat_number" :value="t('profile.field_vat_number')" class="font-medium" />
                                <TextInput id="vat_number" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.vat_number" />
                                <InputError class="mt-2" :message="form.errors.vat_number" />
                            </div>
                        </div>

                        <hr class="border-gray-100" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="city" :value="t('profile.field_city')" class="font-medium" />
                                <TextInput id="city" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.city" :placeholder="t('profile.city_placeholder')" required />
                                <InputError class="mt-2" :message="form.errors.city" />
                            </div>
                            <div>
                                <InputLabel for="phone_number" :value="t('profile.field_phone')" class="font-medium" />
                                <TextInput id="phone_number" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="form.phone_number" placeholder="+371 20000000" />
                                <InputError class="mt-2" :message="form.errors.phone_number" />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="description" :value="t('profile.field_bio_label')" class="font-medium" />
                                <textarea
                                    id="description"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 bg-gray-50 focus:bg-white focus:border-navy focus:ring-navy rounded-md shadow-sm transition-colors"
                                    v-model="form.description"
                                    :placeholder="t('profile.bio_placeholder')"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>
                        </div>
                    </div>

                    <AvatarUpload
                        v-model="form.avatar"
                        :initialAvatar="profile.avatar ? `/storage/${profile.avatar}` : null"
                        :error="form.errors.avatar as string"
                    />
                </div>
            </div>

            <template v-if="isMaster">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <ExperienceEditor
                        v-model="form.experiences"
                        :errors="form.errors"
                    />

                    <PortfolioUploader
                        v-model:newFiles="form.portfolio_images"
                        v-model:imagesToDelete="form.images_to_delete"
                        v-model:existingImages="existingPortfolio"
                        :error="form.errors.portfolio_images as string"
                    />
                </div>
            </template>

            <div class="bg-navy/5 p-6 rounded-xl border border-navy/10 flex items-center justify-between">
                <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                    <div v-if="form.recentlySuccessful" class="flex items-center text-green-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span class="text-sm font-medium">{{ t('profile.saved_message') }}</span>
                    </div>
                    <div v-else class="text-sm text-gray-500">
                        {{ t('profile.unsaved_message') }}
                    </div>
                </Transition>

                <PrimaryButton :class="{ 'opacity-75 cursor-not-allowed': form.processing }" :disabled="form.processing" class="px-8 py-2.5 bg-navy hover:bg-navy-hover">
                    <span v-if="form.processing" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ t('profile.saving_btn') }}
                    </span>
                    <span v-else>{{ t('profile.save_changes_btn') }}</span>
                </PrimaryButton>
            </div>
        </form>
    </section>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
