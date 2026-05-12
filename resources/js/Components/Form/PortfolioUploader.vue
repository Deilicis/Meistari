<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import InputError from '@/Components/Form/InputError.vue';

const { t } = useI18n();

const newFiles = defineModel<File[]>('newFiles', { required: true });
const imagesToDelete = defineModel<string[]>('imagesToDelete', { required: true });
const existingImages = defineModel<string[]>('existingImages', { required: true });

const props = defineProps<{
    error?: string;
}>();

const newPortfolioPreviews = ref<{ url: string, file: File }[]>([]);

watch(newFiles, (files) => {
    if (files.length === 0) {
        newPortfolioPreviews.value.forEach(p => URL.revokeObjectURL(p.url));
        newPortfolioPreviews.value = [];
    }
}, { deep: true });

const handlePortfolioChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files) {
        Array.from(input.files).forEach(file => {
            newFiles.value.push(file);
            newPortfolioPreviews.value.push({
                url: URL.createObjectURL(file),
                file: file
            });
        });
    }
    if (input) input.value = '';
};

const removeNewPortfolioImage = (index: number) => {
    newFiles.value.splice(index, 1);
    URL.revokeObjectURL(newPortfolioPreviews.value[index].url);
    newPortfolioPreviews.value.splice(index, 1);
};

const markExistingImageForDeletion = (path: string, index: number) => {
    imagesToDelete.value.push(path);
    existingImages.value.splice(index, 1);
};
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
        <div class="bg-gray-200 px-6 py-4 border-b border-gray-100 shrink-0">
            <h3 class="text-lg font-semibold text-gray-800">{{ t('forms.portfolio.title') }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ t('forms.portfolio.desc') }}</p>
        </div>

        <div class="p-6 flex-grow flex flex-col">
            <div class="flex-grow">
                <div v-if="existingImages.length > 0" class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">{{ t('forms.portfolio.existing_label') }}</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div v-for="(img, index) in existingImages" :key="'ext-'+index" class="relative group aspect-w-1 aspect-h-1 rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                            <img :src="`/storage/${img}`" class="w-full h-32 object-cover transition-transform group-hover:scale-105" />
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click="markExistingImageForDeletion(img, index)" class="text-white bg-red-600 p-2 rounded-full hover:bg-red-700 shadow-lg transform hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="newPortfolioPreviews.length > 0" class="mb-6 pt-4 border-t border-gray-100">
                    <h4 class="text-sm font-medium text-indigo-700 mb-3">{{ t('forms.portfolio.new_label') }}</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div v-for="(preview, index) in newPortfolioPreviews" :key="'new-'+index" class="relative group aspect-w-1 aspect-h-1 rounded-lg overflow-hidden border-2 border-indigo-200">
                            <img :src="preview.url" class="w-full h-32 object-cover opacity-90" />
                            <span class="absolute top-2 left-2 bg-indigo-600 text-white text-[10px] uppercase font-bold px-2 py-1 rounded shadow-sm">{{ t('forms.portfolio.new_badge') }}</span>
                            <div class="absolute inset-0 bg-indigo-900/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click="removeNewPortfolioImage(index)" class="text-white bg-red-500 p-2 rounded-full hover:bg-red-600 shadow-lg transform hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-auto pt-4">
                <label class="flex flex-col items-center justify-center w-full px-6 py-10 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-colors group">
                    <div class="space-y-2 text-center">
                        <div class="mx-auto flex justify-center items-center w-12 h-12 rounded-full bg-indigo-100 group-hover:bg-indigo-200 transition-colors">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold text-indigo-600">{{ t('forms.portfolio.click') }}</span> {{ t('forms.portfolio.drag') }}
                        </div>
                        <p class="text-xs text-gray-500">{{ t('forms.portfolio.formats') }}</p>
                    </div>
                    <input type="file" multiple class="sr-only" accept="image/*" @change="handlePortfolioChange" />
                </label>
                <InputError class="mt-2" :message="error" />
            </div>
        </div>
    </div>
</template>