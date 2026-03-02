<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import Modal from '@/Components/Common/Modal.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import InputError from '@/Components/Form/InputError.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import SecondaryButton from '@/Components/Form/SecondaryButton.vue';
import TagInput from '@/Components/Tag/TagInput.vue';

const props = defineProps<{
    show: boolean;
    service: any | null;
    categories: any[];
}>();

const emit = defineEmits(['close', 'saved']);

const processing = ref(false);
const validationErrors = ref<Record<string, string[]>>({});

const form = ref({
    id: null as number | null,
    category_id: '' as number | string,
    title: '',
    description: '',
    price: '' as number | string,
    price_type: 'fixed',
    location: [] as string[],
    is_active: true,
});

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        validationErrors.value = {};
        if (props.service) {
            form.value = {
                id: props.service.id,
                category_id: props.service.category?.id || '',
                title: props.service.title,
                description: props.service.description,
                price: props.service.price || '',
                price_type: props.service.price_type,
                location: Array.isArray(props.service.location) ? props.service.location : [],
                is_active: props.service.is_active,
            };
        } else {
            form.value = {
                id: null,
                category_id: '',
                title: '',
                description: '',
                price: '',
                price_type: 'fixed',
                location: [],
                is_active: true,
            };
        }
    }
});

const save = async () => {
    processing.value = true;
    validationErrors.value = {};

    try {
        if (form.value.id) {
            await axios.put(`/api/services/${form.value.id}`, form.value);
            toast.success('Pakalpojums atjaunināts!');
        } else {
            await axios.post('/api/services', form.value);
            toast.success('Jauns pakalpojums pievienots!');
        }
        
        emit('saved');
        emit('close');
    } catch (error: any) {
        if (error.response?.status === 422) {
            validationErrors.value = error.response.data.errors;
            toast.error('Lūdzu, izlabojiet kļūdas formā.');
        } else {
            toast.error('Notika neparedzēta kļūda.');
        }
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Modal :show="show" @close="emit('close')">
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">
                {{ form.id ? 'Rediģēt pakalpojumu' : 'Jauns pakalpojums' }}
            </h2>

            <form @submit.prevent="save" class="space-y-4">
                <div>
                    <InputLabel for="title" value="Nosaukums" />
                    <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" placeholder="Piem., Santehnikas remonts" />
                    <InputError class="mt-2" :message="validationErrors.title?.[0]" />
                </div>

                <div>
                    <InputLabel for="category_id" value="Kategorija" />
                    <select id="category_id" v-model="form.category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                        <option value="" disabled>Izvēlieties kategoriju</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="validationErrors.category_id?.[0]" />
                </div>

                <div>
                    <InputLabel for="description" value="Apraksts" />
                    <textarea id="description" v-model="form.description" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" placeholder="Detalizēts piedāvājuma apraksts..."></textarea>
                    <InputError class="mt-2" :message="validationErrors.description?.[0]" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="price" value="Cena (€)" />
                        <TextInput id="price" v-model="form.price" type="number" step="0.01" class="mt-1 block w-full" placeholder="50.00" />
                        <InputError class="mt-2" :message="validationErrors.price?.[0]" />
                    </div>

                    <div>
                        <InputLabel for="price_type" value="Cenas tips" />
                        <select id="price_type" v-model="form.price_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            <option value="fixed">Fiksēta cena</option>
                            <option value="hourly">Stundas likme</option>
                            <option value="negotiable">Vienojoties</option>
                        </select>
                        <InputError class="mt-2" :message="validationErrors.price_type?.[0]" />
                    </div>
                </div>

                <div>
                    <InputLabel value="Lokācijas (Pilsētas vai 'Attālināti')" />
                    <TagInput 
                        v-model="form.location" 
                        placeholder="Piem., Rīga, visa Latvija..." 
                        :maxTags="10" 
                    />
                    <InputError class="mt-2" :message="validationErrors.location?.[0] || validationErrors['location.0']?.[0]" />
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <Checkbox name="is_active" v-model:checked="form.is_active" />
                        <span class="ml-2 text-sm text-gray-600">Pakalpojums ir publiski redzams</span>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="emit('close')" type="button">Atcelt</SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': processing }" :disabled="processing">
                        {{ processing ? 'Saglabā...' : 'Saglabāt' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>