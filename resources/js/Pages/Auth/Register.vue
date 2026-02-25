<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormField from '@/Components/FormField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: getRoleValue('seeker'),
    profile_type: props.profileTypes && props.profileTypes.length > 0 
        ? getOptionValue(props.profileTypes[0]) 
        : 'individual',
    city: '',
    first_name: '',
    last_name: '',
    company_name: '',
    reg_number: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Reģistrēties" />

        <div class="mb-5 text-center md:text-left">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Izveido kontu</h2>
            <p class="text-sm text-gray-500 mt-1">Pievienojies platformai un sāc darboties jau šodien.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-3.5">
            
            <div>
                <div class="flex bg-gray-100 p-1 rounded-lg shadow-inner mb-1.5">
                    <button
                        type="button"
                        @click="form.role = getRoleValue('seeker')"
                        :class="form.role === getRoleValue('seeker') ? 'bg-white shadow-sm text-navy' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-2 rounded-md text-xs font-bold tracking-wide transition-all duration-200"
                    >
                        MEKLĒTĀJS
                    </button>
                    <button
                        type="button"
                        @click="form.role = getRoleValue('master')"
                        :class="form.role === getRoleValue('master') ? 'bg-white shadow-sm text-navy' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-2 rounded-md text-xs font-bold tracking-wide transition-all duration-200"
                    >
                        MEISTARS
                    </button>
                </div>
                
                <p class="text-sm text-gray-500 leading-tight min-h-[38px] p-2">
                    <template v-if="form.role === getRoleValue('seeker')">
                        Izvēlies šo, ja vēlies <strong>publicēt darba sludinājumus</strong>, meklēt meistarus un saņemt piedāvājumus saviem projektiem.
                    </template>
                    <template v-else>
                        Izvēlies šo, ja esi profesionālis un vēlies <strong>piedāvāt pakalpojumus</strong>, pieteikties darbiem un atrast jaunus klientus.
                    </template>
                </p>
                <InputError class="mt-1" :message="form.errors.role" />
            </div>

            <div>
                <InputLabel value="Reģistrēties kā:" class="mb-2 text-xs text-gray-600" />
                <div class="flex items-center space-x-6 mb-2">
                    <label v-for="type in profileTypes" :key="getOptionValue(type)" class="flex items-center cursor-pointer">
                        <input
                            type="radio"
                            :value="getOptionValue(type)"
                            v-model="form.profile_type"
                            class="text-navy focus:ring-navy border-gray-300 w-4 h-4 cursor-pointer"
                        />
                        <span class="ml-2 text-sm text-gray-700 font-medium">
                            {{ getProfileLabel(type) }}
                        </span>
                    </label>
                </div>
                <InputError class="mt-1" :message="form.errors.profile_type" />
            </div>

            <hr class="border-gray-100 my-2" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <FormField 
                    id="name" 
                    label="Lietotājvārds" 
                    v-model="form.name" 
                    :error="form.errors.name" 
                    autofocus 
                />
                
                <FormField 
                    id="email" 
                    type="email" 
                    label="E-pasts" 
                    v-model="form.email" 
                    :error="form.errors.email" 
                />
            </div>

            <FormField 
                id="city" 
                label="Pilsēta" 
                placeholder="Piemēram, Rīga" 
                v-model="form.city" 
                :error="form.errors.city" 
            />

            <template v-if="String(form.profile_type).toLowerCase() === 'individual'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 animate-fade-in">
                    <FormField id="first_name" label="Vārds" v-model="form.first_name" :error="form.errors.first_name" />
                    <FormField id="last_name" label="Uzvārds" v-model="form.last_name" :error="form.errors.last_name" />
                </div>
            </template>

            <template v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 animate-fade-in">
                    <FormField id="company_name" label="Uzņēmuma nosaukums" v-model="form.company_name" :error="form.errors.company_name" />
                    <FormField id="reg_number" label="Reģistrācijas numurs" v-model="form.reg_number" :error="form.errors.reg_number" />
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <FormField id="password" type="password" label="Parole" v-model="form.password" :error="form.errors.password" />
                <FormField id="password_confirmation" type="password" label="Apstiprināt paroli" v-model="form.password_confirmation" :error="form.errors.password_confirmation" />
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-between pt-2 gap-4">
                <Link :href="route('login')" class="text-sm text-navy hover:text-blue-700 font-semibold underline underline-offset-4">
                    Jau ir konts? Ienākt
                </Link>

                <PrimaryButton class="w-full sm:w-auto bg-navy hover:bg-navy-hover px-8 py-2.5" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reģistrēties
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-3px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>