<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import FormField from '@/Components/Form/FormField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    if (val === 'individual') return 'Privātpersona';
    if (val === 'company') return 'Uzņēmums';
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
        <Head title="Reģistrēties" />

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-navy">Izveido kontu</h2>
            <p class="text-sm text-gray-500 mt-1">Pievienojies platformai un sāc darboties jau šodien.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">

            <!-- Lomas izvēle -->
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
                        Meklētājs
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
                        Meistars
                    </button>
                </div>

                <p class="text-xs text-gray-500 leading-snug bg-gray-50 rounded-lg px-3 py-2.5 border border-gray-100">
                    <template v-if="isSeeker">
                        Publicē darba sludinājumus, meklē meistarus un saņem piedāvājumus saviem projektiem.
                    </template>
                    <template v-else>
                        Piedāvā savus pakalpojumus, piesakies darbiem un atrod jaunus klientus.
                    </template>
                </p>
                <InputError class="mt-1" :message="form.errors.role" />
            </div>

            <!-- Profila veida izvēle -->
            <div>
                <InputLabel value="Reģistrēties kā:" class="mb-2 text-xs text-gray-500 font-medium" />
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

                <!-- Privātpersona: vārds un uzvārds -->
                <div v-if="isIndividual" class="grid grid-cols-2 gap-3">
                    <div>
                        <InputLabel for="first_name" value="Vārds" />
                        <input
                            id="first_name"
                            type="text"
                            v-model="firstName"
                            placeholder="Jānis"
                            class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                            required
                        />
                    </div>
                    <div>
                        <InputLabel for="last_name" value="Uzvārds" />
                        <input
                            id="last_name"
                            type="text"
                            v-model="lastName"
                            placeholder="Bērziņš"
                            class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                            required
                        />
                    </div>
                    <InputError class="col-span-2 -mt-2" :message="form.errors.name" />
                </div>

                <!-- Uzņēmums: uzņēmuma nosaukums -->
                <div v-else>
                    <InputLabel for="company_name" value="Uzņēmuma nosaukums" />
                    <input
                        id="company_name"
                        type="text"
                        v-model="companyName"
                        placeholder="SIA Piemērs"
                        class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                        required
                    />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>

                <!-- E-pasta lauks -->
                <FormField
                    id="email"
                    type="email"
                    label="E-pasts"
                    v-model="form.email"
                    :error="form.errors.email"
                />

                <!-- Paroles lauki -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <FormField id="password" type="password" label="Parole" v-model="form.password" :error="form.errors.password" />
                    <FormField id="password_confirmation" type="password" label="Apstiprināt paroli" v-model="form.password_confirmation" :error="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="pt-2 space-y-3">
                <!-- Reģistrācijas poga -->
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
                        Reģistrē...
                    </span>
                    <span v-else>Reģistrēties</span>
                </PrimaryButton>

                <!-- Saite uz autorizācijas lapu -->
                <p class="text-center text-sm text-gray-500">
                    Jau ir konts?
                    <Link :href="route('login')" class="font-semibold text-navy hover:text-navy-hover underline underline-offset-4 transition-colors">
                        Ienākt
                    </Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>