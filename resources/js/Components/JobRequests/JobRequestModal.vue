<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InputError from '@/Components/Form/InputError.vue';
import PortfolioUploader from '@/Components/Form/PortfolioUploader.vue';
import CategorySelect from '@/Components/Form/CategorySelect.vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import { lv } from 'date-fns/locale';
import { toast } from 'vue-sonner';
import { XMarkIcon, ClipboardDocumentListIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import type { JobRequest, Category } from '@/types/models';

const { t } = useI18n();

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
    deadline: null as string | null,
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
            form.deadline = props.jobRequest.deadline ?? null;
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
        form.setError('location', t('job_requests.location_required_error'));
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
                toast.error(t('job_requests.form_error_toast'));
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
                toast.error(t('job_requests.form_error_toast'));
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
        
        <div class="bg-navy px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-400/20 rounded-lg flex items-center justify-center">
                    <ClipboardDocumentListIcon class="w-4 h-4 text-emerald-300" />
                </div>
                <h2 class="text-base font-bold text-white">
                    {{ jobRequest ? t('job_requests.modal_edit_title') : t('job_requests.modal_create_title') }}
                </h2>
            </div>
            <button @click="closeModal" type="button" class="text-white/60 hover:text-white transition-colors">
                <XMarkIcon class="w-5 h-5" />
            </button>
        </div>

        
        <div class="p-6">
            <form @submit.prevent="submit" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    
                    <div class="md:col-span-2">
                        <InputLabel for="title" :value="t('job_requests.field_title_label')" class="text-gray-700 font-medium" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full focus:border-navy focus:ring-navy"
                            v-model="form.title"
                            :placeholder="t('job_requests.field_title_placeholder')"
                            required
                        />
                        <InputError class="mt-1.5" :message="getError('title')" />
                    </div>

                    
                    <div>
                        <InputLabel for="category" :value="t('job_requests.field_category_label')" class="text-gray-700 font-medium" />
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

                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="budget" :value="t('job_requests.field_budget_label')" class="text-gray-700 font-medium" />
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
                            <InputLabel for="deadline" :value="t('job_requests.field_deadline_label')" class="text-gray-700 font-medium" />
                            <VueDatePicker
                                v-model="form.deadline"
                                model-type="iso"
                                :enable-time-picker="true"
                                format="dd.MM.yyyy HH:mm"
                                :locale="lv"
                                :placeholder="t('job_requests.field_deadline_placeholder')"
                                auto-apply
                                :clearable="true"
                                :teleport="true"
                                input-class-name="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-navy focus:ring-1 focus:ring-navy outline-none"
                                class="mt-1"
                            />
                            <InputError class="mt-1.5" :message="getError('deadline')" />
                        </div>
                    </div>

                    
                    <div class="md:col-span-2">
                        <InputLabel for="description" :value="t('job_requests.field_description_label')" class="text-gray-700 font-medium" />
                        <textarea
                            id="description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm text-sm"
                            v-model="form.description"
                            :placeholder="t('job_requests.field_description_placeholder')"
                            required
                        ></textarea>
                        <InputError class="mt-1.5" :message="getError('description')" />
                    </div>

                    
                    <div class="md:col-span-2">
                        <InputLabel :value="t('job_requests.field_location_label')" class="text-gray-700 font-medium" />
                        <div class="mt-1 space-y-2">
                            <div v-for="(_loc, index) in form.location" :key="index" class="flex gap-2">
                                <TextInput
                                    type="text"
                                    class="block w-full focus:border-navy focus:ring-navy"
                                    v-model="form.location[index]"
                                    :placeholder="t('job_requests.field_location_placeholder')"
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
                                {{ t('job_requests.add_location') }}
                            </button>
                        </div>
                        <InputError class="mt-1.5" :message="getError('location')" />
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

                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        {{ t('common.cancel') }}
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
                        {{ jobRequest ? t('job_requests.save_changes_btn') : t('jobs.publish_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
