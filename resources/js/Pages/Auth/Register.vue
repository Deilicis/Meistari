<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import FormField from '@/Components/Form/FormField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

type EnumOption = string | { name: string; value: string };

const props = defineProps<{
    roles: EnumOption[];
    profileTypes: EnumOption[];
}>();

const getOptionValue = (option: EnumOption): string => {
    if (!option) return '';
    return typeof option === 'string' ? option : option.value;
};

const getProfileLabel = (option: EnumOption): string => {
    const val = getOptionValue(option).toLowerCase();
    if (val === 'individual') return t('auth.register.individual');
    if (val === 'company') return t('auth.register.company');
    return typeof option === 'string' ? option : option.name;
};

const getRoleValue = (searchName: string): string => {
    if (!props.roles || !props.roles.length) return searchName.toLowerCase();
    const found = props.roles.find(r => {
        const val = getOptionValue(r).toLowerCase();
        const sName = searchName.toLowerCase();
        const rName = typeof r === 'string' ? val : r.name.toLowerCase();
        return val === sName || rName === sName;
    });
    return found ? getOptionValue(found) : searchName.toLowerCase();
};

const firstName = ref('');
const lastName = ref('');
const companyName = ref('');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: getRoleValue('seeker'),
    profile_type: props.profileTypes && props.profileTypes.length > 0
        ? getOptionValue(props.profileTypes[0])
        : 'individual',
});

const isSeeker = computed(() => form.role === getRoleValue('seeker'));
const isMaster = computed(() => form.role === getRoleValue('master'));
const isIndividual = computed(() => form.profile_type === 'individual');

const submit = () => {
    form.name = isIndividual.value
        ? [firstName.value, lastName.value].filter(Boolean).join(' ')
        : companyName.value;

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout>
        <Head :title="t('auth.register.title')" />

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-navy">{{ t('auth.register.title') }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ t('auth.register.subtitle') }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">

            
            <div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <button
                        type="button"
                        @click="form.role = getRoleValue('seeker')"
                        class="flex flex-col items-center gap-1.5 py-3 px-4 rounded-xl border-2 text-sm font-semibold transition-all"
                        :class="isSeeker
                            ? 'bg-emerald-50 border-emerald-400 text-emerald-700'
                            : 'bg-gray-50 border-gray-200 text-gray-400 hover:border-gray-300 hover:text-gray-600'"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ t('auth.register.role_seeker') }}
                    </button>
                    <button
                        type="button"
                        @click="form.role = getRoleValue('master')"
                        class="flex flex-col items-center gap-1.5 py-3 px-4 rounded-xl border-2 text-sm font-semibold transition-all"
                        :class="isMaster
                            ? 'bg-amber-50 border-gold text-amber-700'
                            : 'bg-gray-50 border-gray-200 text-gray-400 hover:border-gray-300 hover:text-gray-600'"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ t('auth.register.role_master') }}
                    </button>
                </div>

                <p class="text-xs text-gray-500 leading-snug bg-gray-50 rounded-lg px-3 py-2.5 border border-gray-100">
                    <template v-if="isSeeker">{{ t('auth.register.seeker_desc') }}</template>
                    <template v-else>{{ t('auth.register.master_desc') }}</template>
                </p>
                <InputError class="mt-1" :message="form.errors.role" />
            </div>

            
            <div>
                <InputLabel :value="t('auth.register.register_as')" class="mb-2 text-xs text-gray-500 font-medium" />
                <div class="flex items-center gap-6">
                    <label v-for="type in profileTypes" :key="getOptionValue(type)" class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            :value="getOptionValue(type)"
                            v-model="form.profile_type"
                            class="text-navy focus:ring-navy border-gray-300 w-4 h-4 cursor-pointer"
                        />
                        <span class="text-sm text-gray-700 font-medium">{{ getProfileLabel(type) }}</span>
                    </label>
                </div>
                <InputError class="mt-1" :message="form.errors.profile_type" />
            </div>

            <div class="border-t border-gray-100 pt-4 space-y-4">

                
                <div v-if="isIndividual" class="grid grid-cols-2 gap-3">
                    <div>
                        <InputLabel for="first_name" :value="t('auth.register.first_name')" />
                        <input
                            id="first_name"
                            type="text"
                            v-model="firstName"
                            placeholder="Jānis"
                            class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                        />
                    </div>
                    <div>
                        <InputLabel for="last_name" :value="t('auth.register.last_name')" />
                        <input
                            id="last_name"
                            type="text"
                            v-model="lastName"
                            placeholder="Bērziņš"
                            class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                        />
                    </div>
                    <InputError class="col-span-2 -mt-2" :message="form.errors.name" />
                </div>

                
                <div v-else>
                    <InputLabel for="company_name" :value="t('auth.register.company_name')" />
                    <input
                        id="company_name"
                        type="text"
                        v-model="companyName"
                        placeholder="SIA Piemērs"
                        class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                    />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>

                
                <FormField
                    id="email"
                    type="email"
                    :label="t('auth.register.email_label')"
                    v-model="form.email"
                    :error="form.errors.email"
                />

                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <FormField id="password" type="password" :label="t('auth.register.password_label')" v-model="form.password" :error="form.errors.password" />
                    <FormField id="password_confirmation" type="password" :label="t('auth.register.confirm_password')" v-model="form.password_confirmation" :error="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="pt-2 space-y-3">
                
                <PrimaryButton
                    class="w-full justify-center py-2.5"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ t('auth.register.submitting') }}
                    </span>
                    <span v-else>{{ t('auth.register.submit') }}</span>
                </PrimaryButton>

                
                <p class="text-center text-sm text-gray-500">
                    {{ t('auth.register.has_account') }}
                    <Link :href="route('login')" class="font-semibold text-navy hover:text-navy-hover underline underline-offset-4 transition-colors">
                        {{ t('auth.register.login_link') }}
                    </Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>