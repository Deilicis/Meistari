<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SmartCategoryPicker from '@/Components/Categories/SmartCategoryPicker.vue';
import TagInput from '@/Components/Tag/TagInput.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import InputError from '@/Components/Form/InputError.vue';
import { BriefcaseIcon, ArrowLeftIcon, ArrowRightIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import type { Category } from '@/types/models';
import type { PickerSelection } from '@/types/categorysuggestion';

const { t } = useI18n();

const props = defineProps<{
    categories: Category[];
}>();

const step = ref(1);
const totalSteps = 2;
const showCancelConfirm = ref(false);
const stepErrors = ref<Record<string, string>>({});
const serverErrors = ref<Record<string, string[]>>({});
const processing = ref(false);

const form = ref({
    title: '',
    description: '',
    price: '' as number | string,
    location: [] as string[],
    is_active: true,
});

const parentSelection = ref<PickerSelection | null>(null);
const childSelection  = ref<PickerSelection | null>(null);

const selectedParentCategory = computed(() =>
    parentSelection.value?.type === 'category'
        ? props.categories.find(c => c.id === (parentSelection.value as { type: 'category'; id: number }).id) ?? null
        : null
);
const parentHasChildren = computed(() => (selectedParentCategory.value?.children?.length ?? 0) > 0);
const showChildPicker   = computed(() => parentSelection.value?.type === 'category' && parentHasChildren.value);

watch(parentSelection, () => { childSelection.value = null; });

const getError = (key: string): string | undefined =>
    stepErrors.value[key] ?? serverErrors.value[key]?.[0];

const isDirty = computed(() =>
    form.value.title.trim() !== '' ||
    form.value.description.trim() !== '' ||
    !!parentSelection.value ||
    form.value.location.length > 0
);

const stepLabels = computed(() => [t('services.step_basic'), t('services.step_details')]);

const validateStep1 = (): boolean => {
    stepErrors.value = {};
    if (!form.value.title.trim()) stepErrors.value.title = t('services.validate_title_msg');
    const finalSel = childSelection.value ?? parentSelection.value;
    if (!finalSel) stepErrors.value.category_id = t('services.validate_category_msg');
    if (!form.value.description.trim()) stepErrors.value.description = t('services.validate_description_msg');
    return Object.keys(stepErrors.value).length === 0;
};

const validateStep2 = (): boolean => {
    stepErrors.value = {};
    if (form.value.location.length === 0) stepErrors.value.location = t('services.validate_location_msg');
    return Object.keys(stepErrors.value).length === 0;
};

const nextStep = () => {
    if (step.value === 1 && !validateStep1()) return;
    stepErrors.value = {};
    step.value++;
};

const prevStep = () => {
    stepErrors.value = {};
    step.value--;
};

const submit = async () => {
    if (!validateStep2()) return;

    const finalSel = childSelection.value ?? parentSelection.value;
    const payload: Record<string, any> = { ...form.value };

    if (finalSel?.type === 'category') {
        payload.category_id = finalSel.id;
    } else if (finalSel?.type === 'suggestion') {
        payload.pending_category_suggestion_id = finalSel.id;
    }

    processing.value = true;
    serverErrors.value = {};

    try {
        await axios.post('/api/services', payload);
        const successMsg = finalSel?.type === 'suggestion'
            ? t('services.created_with_suggestion_toast')
            : t('services.created_toast');
        toast.success(successMsg);
        router.visit(route('master.services.index'));
    } catch (error: any) {
        if (error.response?.status === 422) {
            serverErrors.value = error.response.data.errors ?? {};
            toast.error(t('services.form_error_toast'));
            const errs = serverErrors.value;
            if (errs.title || errs.category_id || errs.description || errs.pending_category_suggestion_id) {
                step.value = 1;
            }
        } else {
            toast.error(t('services.unexpected_error_toast'));
        }
    } finally {
        processing.value = false;
    }
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
    <Head :title="t('services.create_title')" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1 bg-gold" />
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <BriefcaseIcon class="w-6 h-6 text-gold" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ t('services.create_title') }}</h1>
                        <p class="text-white/50 text-sm mt-0.5">{{ t('services.create_subtitle') }}</p>
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
                                ? 'bg-gold text-navy'
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
                    <div v-if="idx < totalSteps - 1" class="h-0.5 flex-1 mx-2 mb-4 transition-colors" :class="step > idx + 1 ? 'bg-gold' : 'bg-gray-200'" />
                </template>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">

                <!-- Step 1: Basic info -->
                <div v-if="step === 1" class="space-y-5">
                    <div>
                        <InputLabel :value="t('services.field_title_label')" class="text-gray-700 font-medium mb-1" />
                        <TextInput
                            type="text"
                            class="block w-full focus:border-navy focus:ring-navy"
                            v-model="form.title"
                            :placeholder="t('services.field_title_placeholder')"
                            autofocus
                        />
                        <InputError class="mt-1" :message="getError('title')" />
                    </div>

                    <div>
                        <InputLabel :value="t('services.field_category_label')" class="text-gray-700 font-medium mb-1" />
                        <SmartCategoryPicker
                            v-model="parentSelection"
                            :parent-category-id="null"
                            :placeholder="t('services.field_category_placeholder')"
                        />
                        <div v-if="showChildPicker" class="mt-2">
                            <SmartCategoryPicker
                                v-model="childSelection"
                                :parent-category-id="selectedParentCategory!.id"
                                :placeholder="t('services.field_subcategory_placeholder')"
                            />
                        </div>
                        <InputError class="mt-1" :message="getError('category_id') || getError('pending_category_suggestion_id')" />
                    </div>

                    <div>
                        <InputLabel :value="t('services.field_description_label')" class="text-gray-700 font-medium mb-1" />
                        <textarea
                            v-model="form.description"
                            rows="5"
                            :placeholder="t('services.field_description_placeholder')"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-navy focus:ring-1 focus:ring-navy outline-none resize-y"
                        />
                        <InputError class="mt-1" :message="getError('description')" />
                    </div>
                </div>

                <!-- Step 2: Details -->
                <div v-else-if="step === 2" class="space-y-5">
                    <div>
                        <InputLabel :value="t('services.field_price_label')" class="text-gray-700 font-medium mb-1" />
                        <TextInput
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full focus:border-navy focus:ring-navy"
                            v-model="form.price"
                            :placeholder="t('services.field_price_placeholder')"
                        />
                        <InputError class="mt-1" :message="getError('price')" />
                    </div>

                    <div>
                        <InputLabel :value="t('services.field_locations_label')" class="text-gray-700 font-medium mb-1" />
                        <TagInput
                            v-model="form.location"
                            :placeholder="t('services.field_locations_placeholder')"
                            :maxTags="10"
                        />
                        <InputError class="mt-1" :message="getError('location') || getError('location.0')" />
                    </div>

                    <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                        <Checkbox name="is_active" v-model:checked="form.is_active" />
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ t('services.field_visible_label') }}</p>
                            <p class="text-xs text-gray-500">{{ t('services.field_visible_desc') }}</p>
                        </div>
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
                        @click="isDirty ? showCancelConfirm = true : router.visit(route('master.services.index'))"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-xl hover:border-gray-300 hover:text-gray-800 transition-colors"
                    >
                        <XMarkIcon class="w-4 h-4" />
                        {{ t('services.cancel_btn') }}
                    </button>

                    <button
                        v-if="step < totalSteps"
                        type="button"
                        @click="nextStep"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-white bg-navy hover:bg-navy/90 rounded-xl transition-colors"
                    >
                        {{ t('services.next_btn') }}
                        <ArrowRightIcon class="w-4 h-4" />
                    </button>
                    <button
                        v-else
                        type="button"
                        @click="submit"
                        :disabled="processing"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-white bg-gold hover:bg-yellow-400 text-navy rounded-xl transition-colors disabled:opacity-60"
                    >
                        <CheckIcon class="w-4 h-4" />
                        {{ processing ? t('services.publishing_btn') : t('services.publish_btn') }}
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
                    <h3 class="text-base font-bold text-gray-900 mb-2">{{ t('services.cancel_confirm_title') }}</h3>
                    <p class="text-sm text-gray-500 mb-5">{{ t('services.cancel_confirm_desc') }}</p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showCancelConfirm = false"
                            class="px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-xl hover:border-gray-300 transition-colors"
                        >
                            {{ t('services.cancel_confirm_continue') }}
                        </button>
                        <Link
                            :href="route('master.services.index')"
                            class="px-4 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors"
                        >
                            {{ t('services.cancel_confirm_yes') }}
                        </Link>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
