<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Form/PrimaryButton.vue';
import ExperienceEditor from '@/Components/Form/ExperienceEditor.vue';
import PortfolioUploader from '@/Components/Form/PortfolioUploader.vue';

const { t } = useI18n();

defineProps<{
    form: any;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const existingPortfolio = defineModel<string[]>('existingPortfolio', { required: true });
</script>

<template>
    <div
        role="tabpanel"
        id="tabpanel-master-profile"
        aria-labelledby="tab-master-profile"
        class="space-y-4"
    >
        <form @submit.prevent="emit('submit')" class="space-y-4">

            <!-- Apraksts -->
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 space-y-4">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        {{ t('profile.sections.bio') }}
                    </h3>
                    <div>
                        <InputLabel for="description" :value="t('profile.field_bio_label')" class="font-medium" />
                        <textarea
                            id="description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 bg-gray-50 focus:bg-white focus:border-navy focus:ring-navy rounded-md shadow-sm transition-colors text-sm"
                            v-model="form.description"
                            :placeholder="t('profile.bio_placeholder')"
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>
                </div>
            </div>

            <!-- Pieredze + Portfolio -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <ExperienceEditor
                    v-model="form.experiences"
                    :errors="form.errors"
                />
                <PortfolioUploader
                    v-model:newFiles="form.portfolio_images"
                    v-model:imagesToDelete="form.images_to_delete"
                    v-model:existingImages="existingPortfolio"
                    :error="form.errors.portfolio_images as string"
                />
            </div>

            <!-- Save bar -->
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm px-6 py-4 flex items-center justify-between gap-4">
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                    mode="out-in"
                >
                    <div v-if="form.recentlySuccessful" class="flex items-center text-emerald-600">
                        <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium">{{ t('profile.saved_message') }}</span>
                    </div>
                    <p v-else class="text-sm text-gray-400">{{ t('profile.unsaved_message') }}</p>
                </Transition>

                <PrimaryButton
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-navy hover:bg-navy-hover shrink-0"
                >
                    <span v-if="form.processing" class="flex items-center gap-1.5">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ t('profile.saving_btn') }}
                    </span>
                    <span v-else>{{ t('profile.save_changes_btn') }}</span>
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
