<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InputError from '@/Components/Form/InputError.vue';
import PortfolioUploader from '@/Components/Form/PortfolioUploader.vue';
import CategorySelect from '@/Components/Form/CategorySelect.vue';
import { toast } from 'vue-sonner';
import { XMarkIcon, ClipboardDocumentListIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import type { JobRequest, Category } from '@/types/models';

const props = defineProps<{
    show: boolean;
    jobRequest: JobRequest | null;
    categories: Category[];
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
            form.category_id = props.jobRequest.category_id.toString();
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
        <!-- Header -->
        <div class="bg-navy px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-400/20 rounded-lg flex items-center justify-center">
                    <ClipboardDocumentListIcon class="w-4 h-4 text-emerald-300" />
                </div>
                <h2 class="text-base font-bold text-white">
                    {{ jobRequest ? 'Rediģēt sludinājumu' : 'Jauns darba sludinājums' }}
                </h2>
            </div>
            <button @click="closeModal" type="button" class="text-white/60 hover:text-white transition-colors">
                <XMarkIcon class="w-5 h-5" />
            </button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <form @submit.prevent="submit" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Title (full width) -->
                    <div class="md:col-span-2">
                        <InputLabel for="title" value="Virsraksts *" class="text-gray-700 font-medium" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full focus:border-navy focus:ring-navy"
                            v-model="form.title"
                            placeholder="Piem., Nepieciešams lamināta ieklājējs"
                            required
                        />
                        <InputError class="mt-1.5" :message="getError('title')" />
                    </div>

                    <!-- Category -->
                    <div>
                        <InputLabel for="category" value="Kategorija *" class="text-gray-700 font-medium" />
                        <div class="mt-1">
                            <CategorySelect
                                v-model="form.category_id"
                                :categories="categories"
                                :show-all-option="false"
                                :required="true"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="getError('category_id')" />
                    </div>

                    <!-- Budget + Deadline -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="budget" value="Budžets (€)" class="text-gray-700 font-medium" />
                            <TextInput
                                id="budget"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full focus:border-navy focus:ring-navy"
                                v-model="form.budget"
                                @input="form.budget = ($event.target as HTMLInputElement).value"
                                placeholder="500"
                            />
                            <InputError class="mt-1.5" :message="getError('budget')" />
                        </div>
                        <div>
                            <InputLabel for="deadline" value="Termiņš" class="text-gray-700 font-medium" />
                            <TextInput
                                id="deadline"
                                type="date"
                                class="mt-1 block w-full focus:border-navy focus:ring-navy"
                                v-model="form.deadline"
                            />
                            <InputError class="mt-1.5" :message="getError('deadline')" />
                        </div>
                    </div>

                    <!-- Description (full width) -->
                    <div class="md:col-span-2">
                        <InputLabel for="description" value="Darba apraksts *" class="text-gray-700 font-medium" />
                        <textarea
                            id="description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                            v-model="form.description"
                            placeholder="Aprakstiet nepieciešamo darbu apjomu, detaļas un specifiku..."
                            required
                        ></textarea>
                        <InputError class="mt-1.5" :message="getError('description')" />
                    </div>

                    <!-- Locations (full width) -->
                    <div class="md:col-span-2">
                        <InputLabel value="Darba norises vieta(s) *" class="text-gray-700 font-medium" />
                        <div class="mt-1 space-y-2">
                            <div v-for="(_loc, index) in form.location" :key="index" class="flex gap-2">
                                <TextInput
                                    type="text"
                                    class="block w-full focus:border-navy focus:ring-navy"
                                    v-model="form.location[index]"
                                    placeholder="Pilsēta vai precīza adrese"
                                    required
                                />
                                <button
                                    v-if="form.location.length > 1"
                                    type="button"
                                    @click="removeLocation(index)"
                                    class="flex-shrink-0 w-9 h-9 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition-colors border border-red-200"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                            <button
                                type="button"
                                @click="addLocation"
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-navy hover:text-navy-hover transition-colors"
                            >
                                <PlusIcon class="w-4 h-4" />
                                Pievienot vēl vienu vietu
                            </button>
                        </div>
                        <InputError class="mt-1.5" :message="getError('location')" />
                    </div>

                    <!-- Images (full width) -->
                    <div class="md:col-span-2">
                        <PortfolioUploader
                            v-model:newFiles="form.images"
                            v-model:imagesToDelete="form.images_to_delete"
                            v-model:existingImages="existingImages"
                            :error="getError('images')"
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Atcelt
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 text-sm font-semibold text-white bg-navy rounded-lg hover:bg-navy-hover transition-colors disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ jobRequest ? 'Saglabāt izmaiņas' : 'Publicēt sludinājumu' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
