<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// 1. Tagad mēs pasakām TS, ka dati var būt gan strings, gan objekts
type EnumOption = string | { name: string; value: string };

const props = defineProps<{
    roles: EnumOption[];
    profileTypes: EnumOption[];
}>();

// 2. Viedā funkcija, kas izvelk vērtību jebkurā situācijā
const getOptionValue = (option: EnumOption): string => {
    if (!option) return '';
    return typeof option === 'string' ? option : option.value;
};

// 3. Viedā funkcija, kas iztulko Enum uz Latviešu valodu
const getProfileLabel = (option: EnumOption): string => {
    const val = getOptionValue(option).toLowerCase();
    if (val === 'individual') return 'Privātpersona';
    if (val === 'company') return 'Uzņēmums';
    
    return typeof option === 'string' ? option : option.name;
};

// 4. Lomas izvēles palīgfunkcija
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

        <div class="mb-8 text-center md:text-left">
            <h2 class="text-3xl font-bold text-gray-900">Izveido kontu</h2>
            <p class="text-sm text-gray-500 mt-2">Pievienojies platformai un sāc darboties jau šodien.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            
            <div class="flex bg-gray-100 p-1.5 rounded-xl mb-6 shadow-inner">
                <button
                    type="button"
                    @click="form.role = getRoleValue('seeker')"
                    :class="form.role === getRoleValue('seeker') ? 'bg-white shadow-sm text-[#0a192f]' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 py-3 rounded-lg text-sm font-bold tracking-wide transition-all duration-200"
                >
                    MEKLĒTĀJS
                </button>
                <button
                    type="button"
                    @click="form.role = getRoleValue('master')"
                    :class="form.role === getRoleValue('master') ? 'bg-white shadow-sm text-[#0a192f]' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 py-3 rounded-lg text-sm font-bold tracking-wide transition-all duration-200"
                >
                    MEISTARS
                </button>
            </div>
            <InputError class="mt-1" :message="form.errors.role" />

            <div>
                <InputLabel value="Reģistrēties kā:" class="mb-2" />
                <div class="flex items-center space-x-6">
                    <label v-for="type in profileTypes" :key="getOptionValue(type)" class="flex items-center cursor-pointer">
                        <input
                            type="radio"
                            :value="getOptionValue(type)"
                            v-model="form.profile_type"
                            class="text-[#0a192f] focus:ring-[#0a192f] border-gray-300 w-4 h-4 cursor-pointer"
                        />
                        <span class="ml-2 text-sm text-gray-700 font-medium">
                            {{ getProfileLabel(type) }}
                        </span>
                    </label>
                </div>
                <InputError class="mt-1" :message="form.errors.profile_type" />
            </div>

            <hr class="border-gray-100 my-4" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="name" value="Lietotājvārds" />
                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="email" value="E-pasts" />
                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>
            </div>

            <div>
                <InputLabel for="city" value="Pilsēta" />
                <TextInput id="city" type="text" class="mt-1 block w-full" v-model="form.city" required placeholder="Piemēram, Rīga" />
                <InputError class="mt-1" :message="form.errors.city" />
            </div>

            <template v-if="String(form.profile_type).toLowerCase() === 'individual'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fade-in">
                    <div>
                        <InputLabel for="first_name" value="Vārds" />
                        <TextInput id="first_name" type="text" class="mt-1 block w-full" v-model="form.first_name" required />
                        <InputError class="mt-1" :message="form.errors.first_name" />
                    </div>
                    <div>
                        <InputLabel for="last_name" value="Uzvārds" />
                        <TextInput id="last_name" type="text" class="mt-1 block w-full" v-model="form.last_name" required />
                        <InputError class="mt-1" :message="form.errors.last_name" />
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fade-in">
                    <div>
                        <InputLabel for="company_name" value="Uzņēmuma nosaukums" />
                        <TextInput id="company_name" type="text" class="mt-1 block w-full" v-model="form.company_name" required />
                        <InputError class="mt-1" :message="form.errors.company_name" />
                    </div>
                    <div>
                        <InputLabel for="reg_number" value="Reģistrācijas numurs" />
                        <TextInput id="reg_number" type="text" class="mt-1 block w-full" v-model="form.reg_number" required />
                        <InputError class="mt-1" :message="form.errors.reg_number" />
                    </div>
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="password" value="Parole" />
                    <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required />
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>
                <div>
                    <InputLabel for="password_confirmation" value="Apstiprināt paroli" />
                    <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required />
                    <InputError class="mt-1" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <Link :href="route('login')" class="text-sm text-[#0a192f] hover:text-blue-700 font-semibold underline underline-offset-4">
                    Jau ir konts? Ienākt
                </Link>

                <PrimaryButton class="bg-[#0a192f] hover:bg-blue-900 px-8 py-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reģistrēties
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
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