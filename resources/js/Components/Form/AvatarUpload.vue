<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import InputError from '@/Components/Form/InputError.vue';

const props = defineProps<{
    initialAvatar: string | null;
    error?: string;
}>();

const formAvatar = defineModel<File | string | null>({ required: true });

const preview = ref<string | null>(props.initialAvatar);

watch(() => props.initialAvatar, (newVal) => {
    preview.value = newVal;
});

const handleAvatarChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        formAvatar.value = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeAvatar = () => {
    formAvatar.value = 'delete';
    preview.value = null;
    nextTick(() => {
        const fileInput = document.getElementById('avatar-upload') as HTMLInputElement;
        if (fileInput) fileInput.value = '';
    });
};
</script>

<template>
    <div class="flex flex-col items-center justify-start space-y-4 pt-4 lg:pt-0 lg:border-l lg:border-gray-100 lg:pl-8">
        <div class="relative group">
            <img v-if="preview" :src="preview" alt="Avatar" class="h-56 w-56 lg:h-64 lg:w-64 rounded-full object-cover border-4 border-white shadow-lg" />
            <div v-else class="h-56 w-56 lg:h-64 lg:w-64 rounded-full bg-slate-100 flex items-center justify-center border-4 border-white shadow-md text-gray-400">
                <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
        </div>

        <div class="flex flex-col items-center w-full max-w-[200px] gap-2 mt-4">
            <label class="w-full text-center cursor-pointer bg-navy hover:bg-navy-hover px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white transition-colors">
                <span>{{ preview ? 'Mainīt bildi' : 'Augšupielādēt bildi' }}</span>
                <input id="avatar-upload" type="file" class="sr-only" accept="image/*" @change="handleAvatarChange" />
            </label>
            <button v-if="preview" type="button" @click="removeAvatar" class="text-sm text-red-600 hover:text-red-800">
                Noņemt bildi
            </button>
        </div>
        <InputError class="mt-1 text-center" :message="error" />
    </div>
</template>