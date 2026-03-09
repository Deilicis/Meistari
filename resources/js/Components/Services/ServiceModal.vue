<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import Modal from '@/Components/Common/Modal.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import InputError from '@/Components/Form/InputError.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import TagInput from '@/Components/Tag/TagInput.vue';
import { XMarkIcon, WrenchScrewdriverIcon } from '@heroicons/vue/24/outline';
import type { Service, Category } from '@/types/models';

const props = defineProps<{
    show: boolean;
    service: Service | null;
    categories: Category[];
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
        <!-- Galvene -->
        <div class="bg-navy px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-gold/20 rounded-lg flex items-center justify-center">
                    <WrenchScrewdriverIcon class="w-4 h-4 text-gold" />
                </div>
                <h2 class="text-base font-bold text-white">
                    {{ form.id ? 'Rediģēt pakalpojumu' : 'Jauns pakalpojums' }}
                </h2>
            </div>
            <button @click="emit('close')" type="button" class="text-white/60 hover:text-white transition-colors">
                <XMarkIcon class="w-5 h-5" />
            </button>
        </div>

        <!-- Saturs -->
        <div class="p-6">
            <form @submit.prevent="save" class="space-y-5">

                <!-- Nosaukums -->
                <div>
                    <InputLabel for="title" value="Nosaukums" class="text-gray-700 font-medium" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full focus:border-navy focus:ring-navy"
                        placeholder="Piem., Santehnikas remonts"
                    />
                    <InputError class="mt-1.5" :message="validationErrors.title?.[0]" />
                </div>

                <!-- Kategorija -->
                <div>
                    <InputLabel for="category_id" value="Kategorija" class="text-gray-700 font-medium" />
                    <select
                        id="category_id"
                        v-model="form.category_id"
                        class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                    >
                        <option value="" disabled>Izvēlieties kategoriju</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <InputError class="mt-1.5" :message="validationErrors.category_id?.[0]" />
                </div>

                <!-- Apraksts -->
                <div>
                    <InputLabel for="description" value="Apraksts" class="text-gray-700 font-medium" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                        placeholder="Detalizēts piedāvājuma apraksts..."
                    ></textarea>
                    <InputError class="mt-1.5" :message="validationErrors.description?.[0]" />
                </div>

                <!-- Cena un cenas tips -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="price" value="Cena (€)" class="text-gray-700 font-medium" />
                        <TextInput
                            id="price"
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full focus:border-navy focus:ring-navy"
                            placeholder="50.00"
                        />
                        <InputError class="mt-1.5" :message="validationErrors.price?.[0]" />
                    </div>

                    <div>
                        <InputLabel for="price_type" value="Cenas tips" class="text-gray-700 font-medium" />
                        <select
                            id="price_type"
                            v-model="form.price_type"
                            class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                        >
                            <option value="fixed">Fiksēta cena</option>
                            <option value="hourly">Stundas likme</option>
                            <option value="negotiable">Vienojoties</option>
                        </select>
                        <InputError class="mt-1.5" :message="validationErrors.price_type?.[0]" />
                    </div>
                </div>

                <!-- Lokācijas -->
                <div>
                    <InputLabel value="Lokācijas (Pilsētas vai 'Attālināti')" class="text-gray-700 font-medium" />
                    <TagInput
                        v-model="form.location"
                        placeholder="Piem., Rīga, visa Latvija..."
                        :maxTags="10"
                    />
                    <InputError class="mt-1.5" :message="validationErrors.location?.[0] || validationErrors['location.0']?.[0]" />
                </div>

                <!-- Redzamības izvēle -->
                <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <Checkbox name="is_active" v-model:checked="form.is_active" />
                    <div>
                        <p class="text-sm font-medium text-gray-700">Publiski redzams</p>
                        <p class="text-xs text-gray-500">Pakalpojums būs pieejams meklēšanā</p>
                    </div>
                </div>

                <!-- Darbību pogas -->
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Atcelt
                    </button>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="px-5 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy-hover transition-colors disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ processing ? 'Saglabā...' : 'Saglabāt' }}
                    </button>
                </div>

            </form>
        </div>
    </Modal>
</template>