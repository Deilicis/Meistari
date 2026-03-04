<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InputError from '@/Components/Form/InputError.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import SecondaryButton from '@/Components/Form/SecondaryButton.vue';
import PortfolioUploader from '@/Components/Form/PortfolioUploader.vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    show: boolean;
    jobRequest: any | null;
    categories: any[];
}>();

const emit = defineEmits(['close', 'saved']);

const existingImages = ref<string[]>([]);

const form = useForm({
    _method: 'post',
    title: '',
    description: '',
    category_id: '',
    budget: '',
    deadline: '',
    location: [''],
    images: [] as File[],
    images_to_delete: [] as string[],
});

const getError = (key: string): string | undefined => {
    return (form.errors as Record<string, string>)[key];
};

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.jobRequest) {
            form._method = 'put';
            form.title = props.jobRequest.title;
            form.description = props.jobRequest.description;
            form.category_id = props.jobRequest.category_id;
            form.budget = props.jobRequest.budget ? props.jobRequest.budget.toString() : '';
            form.deadline = props.jobRequest.deadline ? props.jobRequest.deadline.split('T')[0] : '';
            form.location = props.jobRequest.location?.length > 0 ? [...props.jobRequest.location] : [''];
            existingImages.value = props.jobRequest.images ? [...props.jobRequest.images] : [];
        } else {
            form._method = 'post';
            form.reset();
            form.location = [''];
            existingImages.value = [];
        }
        form.clearErrors();
        form.images = [];
        form.images_to_delete = [];
    }
});

const addLocation = () => form.location.push('');
const removeLocation = (index: number) => form.location.splice(index, 1);

const submit = () => {
    const cleanLocations = form.location.filter(loc => loc.trim() !== '');
    if (cleanLocations.length === 0) {
        form.setError('location', 'Norādiet vismaz vienu vietu.');
        return;
    }
    
    const originalLocations = [...form.location];
    form.location = cleanLocations;

    if (props.jobRequest) {
        form.post(`/api/job-requests/${props.jobRequest.id}`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                emit('saved');
                emit('close');
            },
            onError: () => {
                toast.error('Lūdzu, pārbaudiet formu un izlabojiet kļūdas.');
                form.location = originalLocations;
            },
        });
    } else {
        form.post(route('api.job-requests.store'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                emit('saved');
                emit('close');
            },
            onError: () => {
                toast.error('Lūdzu, pārbaudiet formu un izlabojiet kļūdas.');
                form.location = originalLocations;
            },
        });
    }
};

const closeModal = () => {
    emit('close');
    form.reset();
};
</script>

<template>
    <Modal :show="show" @close="closeModal" maxWidth="2xl">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">
                {{ jobRequest ? 'Rediģēt sludinājumu' : 'Izveidot jaunu sludinājumu' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <InputLabel for="title" value="Virsraksts *" />
                        <TextInput id="title" type="text" class="mt-1 block w-full" v-model="form.title" placeholder="Piem., Nepieciešams lamināta ieklājējs" required />
                        <InputError class="mt-2" :message="getError('title')" />
                    </div>

                    <div>
                        <InputLabel for="category" value="Kategorija *" />
                        <select id="category" v-model="form.category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="" disabled>Izvēlieties kategoriju</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="getError('category_id')" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="budget" value="Budžets (€)" />
                            <TextInput 
                                id="budget" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                class="mt-1 block w-full" 
                                v-model="form.budget" 
                                @input="form.budget = $event.target.value.toString()"
                                placeholder="Piem., 500" 
                            />
                            <InputError class="mt-2" :message="getError('budget')" />
                        </div>
                        <div>
                            <InputLabel for="deadline" value="Termiņš" />
                            <TextInput id="deadline" type="date" class="mt-1 block w-full" v-model="form.deadline" />
                            <InputError class="mt-2" :message="getError('deadline')" />
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel for="description" value="Darba apraksts *" />
                        <textarea id="description" rows="5" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" v-model="form.description" placeholder="Aprakstiet nepieciešamo darbu apjomu, detaļas un specifiku..." required></textarea>
                        <InputError class="mt-2" :message="getError('description')" />
                    </div>

                    <div class="md:col-span-2 space-y-3">
                        <InputLabel value="Darba norises vieta(s) *" />
                        <div v-for="(loc, index) in form.location" :key="index" class="flex gap-2">
                            <TextInput type="text" class="block w-full" v-model="form.location[index]" placeholder="Pilsēta vai precīza adrese" required />
                            <button v-if="form.location.length > 1" type="button" @click="removeLocation(index)" class="px-3 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200">Dzēst</button>
                        </div>
                        <button type="button" @click="addLocation" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Pievienot vēl vienu vietu</button>
                        <InputError class="mt-1" :message="getError('location')" />
                    </div>

                    <div class="md:col-span-2">
                        <PortfolioUploader 
                            v-model:newFiles="form.images"
                            v-model:imagesToDelete="form.images_to_delete"
                            v-model:existingImages="existingImages"
                            :error="getError('images')"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 border-t border-gray-100 pt-6">
                    <SecondaryButton @click="closeModal">Atcelt</SecondaryButton>
                    <PrimaryButton class="bg-navy hover:bg-navy-hover" :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                        <span v-if="form.processing">Saglabā...</span>
                        <span v-else>{{ jobRequest ? 'Saglabāt izmaiņas' : 'Publicēt sludinājumu' }}</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>