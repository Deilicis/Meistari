<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import { lv } from 'date-fns/locale';
import '@vuepic/vue-datepicker/dist/main.css';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SmartCategoryPicker from '@/Components/Categories/SmartCategoryPicker.vue';
import PortfolioUploader from '@/Components/Form/PortfolioUploader.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import InputError from '@/Components/Form/InputError.vue';
import { ClipboardDocumentListIcon, PlusIcon, TrashIcon, ArrowLeftIcon, ArrowRightIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import type { Category } from '@/types/models';
import type { PickerSelection } from '@/types/categorysuggestion';

const { t } = useI18n();

const props = defineProps<{
    categories: Category[];
}>();

const step = ref(1);
const totalSteps = 3;
const showCancelConfirm = ref(false);
const stepErrors = ref<Record<string, string>>({});
const existingImages = ref<string[]>([]);

const form = useForm({
    title: '',
    description: '',
    category_id: '' as string | number,
    pending_category_suggestion_id: null as number | null,
    budget: '',
    deadline: null as string | null,
    location: [''],
    images: [] as File[],
    images_to_delete: [] as string[],
});

const parentSelection = ref<PickerSelection | null>(null);
const childSelection = ref<PickerSelection | null>(null);

const selectedParentCategory = computed(() =>
    parentSelection.value?.type === 'category'
        ? props.categories.find(c => c.id === (parentSelection.value as { type: 'category'; id: number }).id) ?? null
        : null
);
const parentHasChildren = computed(() => (selectedParentCategory.value?.children?.length ?? 0) > 0);
const showChildPicker = computed(() => parentSelection.value?.type === 'category' && parentHasChildren.value);

watch(parentSelection, () => { childSelection.value = null; });

const getError = (key: string): string | undefined =>
    (form.errors as Record<string, string>)[key] ?? stepErrors.value[key];

const isDirty = computed(() =>
    form.title.trim() !== '' ||
    form.description.trim() !== '' ||
    !!parentSelection.value ||
    form.budget.trim() !== '' ||
    form.deadline !== null ||
    form.location.some(l => l.trim() !== '') ||
    form.images.length > 0
);

const stepLabels = computed(() => [t('jobs.step_basic'), t('jobs.step_details'), t('jobs.step_photos')]);

const validateStep1 = (): boolean => {
    stepErrors.value = {};
    if (!form.title.trim()) stepErrors.value.title = t('jobs.validate_title_msg');
    const finalSel = childSelection.value ?? parentSelection.value;
    if (!finalSel) stepErrors.value.category_id = t('jobs.validate_category_msg');
    if (!form.description.trim()) stepErrors.value.description = t('jobs.validate_description_msg');
    return Object.keys(stepErrors.value).length === 0;
};

const validateStep2 = (): boolean => {
    stepErrors.value = {};
    const filled = form.location.filter(l => l.trim());
    if (filled.length === 0) stepErrors.value.location = t('jobs.validate_location_msg');
    return Object.keys(stepErrors.value).length === 0;
};

const nextStep = () => {
    if (step.value === 1 && !validateStep1()) return;
    if (step.value === 2 && !validateStep2()) return;
    stepErrors.value = {};
    step.value++;
};

const prevStep = () => {
    stepErrors.value = {};
    step.value--;
};

const addLocation = () => form.location.push('');
const removeLocation = (i: number) => form.location.splice(i, 1);

const submit = () => {
    stepErrors.value = {};
    const cleanLocations = form.location.filter(l => l.trim());
    const original = [...form.location];
    form.location = cleanLocations;

    const finalSel = childSelection.value ?? parentSelection.value;
    if (finalSel?.type === 'category') {
        form.category_id = finalSel.id;
        form.pending_category_suggestion_id = null;
    } else if (finalSel?.type === 'suggestion') {
        form.pending_category_suggestion_id = finalSel.id;
        form.category_id = '';
    }

    form.post(route('api.job-requests.store'), {
        forceFormData: true,
        onError: () => {
            form.location = original;
            if (form.errors.title || form.errors.category_id || form.errors.description || form.errors.pending_category_suggestion_id) {
                step.value = 1;
            } else if (form.errors.location || form.errors.budget || form.errors.deadline) {
                step.value = 2;
            }
        },
    });
};

const handleBeforeUnload = (e: BeforeUnloadEvent) => {
    if (isDirty.value) {
        e.preventDefault();
        e.returnValue = '';
    }
};

onMounted(() => window.addEventListener('beforeunload', handleBeforeUnload));
onUnmounted(() => window.removeEventListener('beforeunload', handleBeforeUnload));
</script>

<template>
    <Head :title="t('jobs.create_title')" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1 bg-emerald-400" />
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <ClipboardDocumentListIcon class="w-6 h-6 text-emerald-400" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ t('jobs.create_title') }}</h1>
                        <p class="text-white/50 text-sm mt-0.5">{{ t('jobs.create_subtitle') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">

            <!-- Progress steps -->
            <div class="flex items-center mb-8">
                <template v-for="(label, idx) in stepLabels" :key="idx">
                    <div class="flex flex-col items-center gap-1 flex-1">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                            :class="step > idx + 1
                                ? 'bg-emerald-500 text-white'
                                : step === idx + 1
                                    ? 'bg-navy text-white'
                                    : 'bg-gray-100 text-gray-400'"
                        >
                            <CheckIcon v-if="step > idx + 1" class="w-4 h-4" />
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <span class="text-xs font-medium hidden sm:block" :class="step === idx + 1 ? 'text-navy' : 'text-gray-400'">
                            {{ label }}
                        </span>
                    </div>
                    <div v-if="idx < totalSteps - 1" class="h-0.5 flex-1 mx-2 mb-4 transition-colors" :class="step > idx + 1 ? 'bg-emerald-500' : 'bg-gray-200'" />
                </template>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">

                <!-- Step 1 -->
                <div v-if="step === 1" class="space-y-5">
                    <div>
                        <InputLabel :value="t('jobs.field_title_label')" class="text-gray-700 font-medium mb-1" />
                        <TextInput
                            type="text"
                            class="block w-full focus:border-navy focus:ring-navy"
                            v-model="form.title"
                            :placeholder="t('jobs.field_title_placeholder')"
                            autofocus
                        />
                        <InputError class="mt-1" :message="getError('title')" />
                    </div>

                    <div>
                        <InputLabel :value="t('jobs.field_category_label')" class="text-gray-700 font-medium mb-1" />
                        <SmartCategoryPicker
                            v-model="parentSelection"
                            :parent-category-id="null"
                            :placeholder="t('jobs.field_category_placeholder')"
                        />
                        <div v-if="showChildPicker" class="mt-2">
                            <SmartCategoryPicker
                                v-model="childSelection"
                                :parent-category-id="selectedParentCategory!.id"
                                :placeholder="t('jobs.field_subcategory_placeholder')"
                            />
                        </div>
                        <InputError class="mt-1" :message="getError('category_id') || getError('pending_category_suggestion_id')" />
                    </div>

                    <div>
                        <InputLabel :value="t('jobs.field_description_label')" class="text-gray-700 font-medium mb-1" />
                        <textarea
                            v-model="form.description"
                            rows="5"
                            :placeholder="t('jobs.field_description_placeholder')"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-navy focus:ring-1 focus:ring-navy outline-none resize-y"
                        />
                        <InputError class="mt-1" :message="getError('description')" />
                    </div>
                </div>

                <!-- Step 2 -->
                <div v-else-if="step === 2" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <InputLabel :value="t('jobs.field_budget_label')" class="text-gray-700 font-medium mb-1" />
                            <TextInput
                                type="number"
                                step="0.01"
                                min="0"
                                class="block w-full focus:border-navy focus:ring-navy"
                                v-model="form.budget"
                                placeholder="500"
                            />
                            <InputError class="mt-1" :message="getError('budget')" />
                        </div>
                        <div>
                            <InputLabel :value="t('jobs.field_deadline_label')" class="text-gray-700 font-medium mb-1" />
                            <VueDatePicker
                                v-model="form.deadline"
                                model-type="iso"
                                :enable-time-picker="false"
                                format="dd.MM.yyyy"
                                :locale="lv"
                                :placeholder="t('jobs.field_deadline_placeholder')"
                                auto-apply
                                :clearable="true"
                                :teleport="true"
                                input-class-name="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-navy focus:ring-1 focus:ring-navy outline-none"
                            />
                            <InputError class="mt-1" :message="getError('deadline')" />
                        </div>
                    </div>

                    <div>
                        <InputLabel :value="t('jobs.field_location_label')" class="text-gray-700 font-medium mb-1" />
                        <div class="space-y-2">
                            <div
                                v-for="(_, i) in form.location"
                                :key="i"
                                class="flex items-center gap-2"
                            >
                                <TextInput
                                    type="text"
                                    class="flex-1 focus:border-navy focus:ring-navy"
                                    v-model="form.location[i]"
                                    :placeholder="t('jobs.field_location_placeholder')"
                                />
                                <button
                                    v-if="form.location.length > 1"
                                    type="button"
                                    @click="removeLocation(i)"
                                    class="p-1.5 text-gray-400 hover:text-red-500 transition-colors"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="addLocation"
                            class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-navy hover:text-navy/70 transition-colors"
                        >
                            <PlusIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                            {{ t('jobs.add_location') }}
                        </button>
                        <InputError class="mt-1" :message="getError('location')" />
                    </div>
                </div>

                <!-- Step 3 -->
                <div v-else-if="step === 3" class="space-y-5">
                    <div>
                        <InputLabel :value="t('jobs.field_photos_label')" class="text-gray-700 font-medium mb-1" />
                        <p class="text-xs text-gray-400 mb-3">{{ t('jobs.field_photos_hint') }}</p>
                        <PortfolioUploader
                            v-model:newFiles="form.images"
                            v-model:imagesToDelete="form.images_to_delete"
                            v-model:existingImages="existingImages"
                            :error="getError('images')"
                        />
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                    <button
                        v-if="step > 1"
                        type="button"
                        @click="prevStep"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-xl hover:border-gray-300 hover:text-gray-800 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        {{ t('profile.back_btn') }}
                    </button>
                    <button
                        v-else
                        type="button"
                        @click="isDirty ? showCancelConfirm = true : router.visit(route('seeker.job-requests.index'))"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-xl hover:border-gray-300 hover:text-gray-800 transition-colors"
                    >
                        <XMarkIcon class="w-4 h-4" />
                        {{ t('jobs.cancel_btn') }}
                    </button>

                    <button
                        v-if="step < totalSteps"
                        type="button"
                        @click="nextStep"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-white bg-navy hover:bg-navy/90 rounded-xl transition-colors"
                    >
                        {{ t('jobs.next_btn') }}
                        <ArrowRightIcon class="w-4 h-4" />
                    </button>
                    <button
                        v-else
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded-xl transition-colors disabled:opacity-60"
                    >
                        <CheckIcon class="w-4 h-4" />
                        {{ form.processing ? t('jobs.publishing_btn') : t('jobs.publish_btn') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Cancel confirmation overlay -->
        <Teleport to="body">
            <div
                v-if="showCancelConfirm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                @click.self="showCancelConfirm = false"
            >
                <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full mx-4">
                    <h3 class="text-base font-bold text-gray-900 mb-2">{{ t('jobs.cancel_confirm_title') }}</h3>
                    <p class="text-sm text-gray-500 mb-5">{{ t('jobs.cancel_confirm_desc') }}</p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showCancelConfirm = false"
                            class="px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-xl hover:border-gray-300 transition-colors"
                        >
                            {{ t('jobs.cancel_confirm_continue') }}
                        </button>
                        <Link
                            :href="route('seeker.job-requests.index')"
                            class="px-4 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors"
                        >
                            {{ t('jobs.cancel_confirm_yes') }}
                        </Link>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
