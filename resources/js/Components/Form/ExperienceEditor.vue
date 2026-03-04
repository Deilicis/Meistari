<script setup lang="ts">
import InputLabel from '@/Components/Form/InputLabel.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InputError from '@/Components/Form/InputError.vue';

const experiences = defineModel<any[]>({ required: true });

const props = defineProps<{
    errors: Record<string, string>;
}>();

const getError = (key: string): string | undefined => {
    return props.errors[key];
};

const addExperience = () => {
    experiences.value.push({ title: '', years: '', description: '' });
};

const removeExperience = (index: number) => {
    experiences.value.splice(index, 1);
};
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
        <div class="bg-gray-200 px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Profesionālā pieredze</h3>
                <p class="text-xs text-gray-500 mt-1">Norādiet savas prasmes un pieredzi dažādās jomās.</p>
            </div>
            <button type="button" @click="addExperience" class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-50 focus:outline-none transition-colors text-sm font-medium shrink-0 ml-4">
                + Pievienot
            </button>
        </div>
        
        <div class="p-6 flex-grow flex flex-col">
            <div v-if="experiences.length === 0" class="bg-blue-50/50 p-8 rounded-lg border-2 border-dashed border-blue-200 text-center flex-grow flex flex-col items-center justify-center">
                <svg class="mx-auto h-12 w-12 text-blue-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <h4 class="text-sm font-medium text-gray-900">Nav pievienota pieredze</h4>
                <p class="text-sm text-gray-500 mt-1 mb-4">Klienti uzticas meistariem, kuri norāda savu darba pieredzi.</p>
                <button type="button" @click="addExperience" class="text-sm text-indigo-600 font-semibold hover:text-indigo-800">
                    Pievienot pirmo ierakstu &rarr;
                </button>
            </div>

            <div class="space-y-4">
                <div v-for="(exp, index) in experiences" :key="index" class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm relative group hover:border-indigo-300 transition-colors">
                    <button type="button" @click="removeExperience(index)" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 p-1 rounded-full hover:bg-red-50 transition-colors" title="Dzēst">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-3 pr-8">
                        <div class="sm:col-span-3">
                            <InputLabel :for="'exp_title_'+index" value="Joma vai Amats" class="text-xs text-gray-500 uppercase tracking-wider" />
                            <TextInput :id="'exp_title_'+index" type="text" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="exp.title" placeholder="Piem., Elektriķis" required />
                            <InputError class="mt-1" :message="getError(`experiences.${index}.title`)" />
                        </div>
                        <div>
                            <InputLabel :for="'exp_years_'+index" value="Pieredze (gadi)" class="text-xs text-gray-500 uppercase tracking-wider" />
                            <TextInput :id="'exp_years_'+index" type="number" min="0" max="70" class="mt-1 block w-full bg-gray-50 focus:bg-white" v-model="exp.years" required />
                            <InputError class="mt-1" :message="getError(`experiences.${index}.years`)" />
                        </div>
                    </div>
                    <div>
                        <InputLabel :for="'exp_desc_'+index" value="Apraksts" class="text-xs text-gray-500 uppercase tracking-wider" />
                        <textarea :id="'exp_desc_'+index" rows="2" class="mt-1 block w-full border-gray-300 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" v-model="exp.description" placeholder="Īsi aprakstiet veiktos darbus..."></textarea>
                        <InputError class="mt-1" :message="getError(`experiences.${index}.description`)" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>