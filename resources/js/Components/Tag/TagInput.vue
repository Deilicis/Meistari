<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    modelValue: string[];
    placeholder?: string;
    maxTags?: number;
}>();

const emit = defineEmits(['update:modelValue']);

const newTag = ref('');
const inputRef = ref<HTMLInputElement | null>(null);

const addTag = () => {
    const tag = newTag.value.trim();
    
    if (tag && !props.modelValue.includes(tag)) {
        if (props.maxTags && props.modelValue.length >= props.maxTags) {
            return;
        }
        emit('update:modelValue', [...props.modelValue, tag]);
    }
    newTag.value = '';
};

const removeTag = (index: number) => {
    const updatedTags = [...props.modelValue];
    updatedTags.splice(index, 1);
    emit('update:modelValue', updatedTags);
};

const handleBackspace = () => {
    if (newTag.value === '' && props.modelValue.length > 0) {
        removeTag(props.modelValue.length - 1);
    }
};

const focusInput = () => {
    inputRef.value?.focus();
};
</script>

<template>
    <div 
        class="mt-1 flex flex-wrap items-center gap-2 w-full border-gray-300 focus-within:border-indigo-500 focus-within:ring-indigo-500 rounded-md shadow-sm border p-1.5 bg-white cursor-text min-h-[42px] transition-colors"
        @click="focusInput"
    >
        <span 
            v-for="(tag, index) in modelValue" 
            :key="index"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-sm font-medium bg-indigo-50 text-indigo-700 border border-indigo-200"
        >
            {{ tag }}
            <button 
                type="button" 
                @click.stop="removeTag(index)"
                class="text-indigo-400 hover:text-indigo-900 focus:outline-none ml-1"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </span>
        
        <input 
            ref="inputRef"
            v-model="newTag"
            type="text"
            class="flex-1 outline-none border-none p-1 focus:ring-0 text-sm min-w-[150px] bg-transparent"
            :placeholder="modelValue.length === 0 ? (placeholder || 'Ievadi un spied Enter') : ''"
            @keydown.enter.prevent="addTag"
            @keydown.prevent.comma="addTag"
            @keydown.backspace="handleBackspace"
        />
    </div>
    <p class="mt-1 text-xs text-gray-500">Rakstiet un nospiediet <b>Enter</b> vai komatu, lai pievienotu.</p>
</template>