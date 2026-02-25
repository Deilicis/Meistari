<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

defineProps<{
    id: string;
    label: string;
    type?: string;
    error?: string;
    placeholder?: string;
    autofocus?: boolean;
}>();

const model = defineModel<string>({ required: true });
</script>

<template>
    <div>
        <InputLabel :for="id" :value="label" />
        <TextInput 
            :id="id" 
            :type="type || 'text'" 
            class="mt-1 block w-full" 
            v-model="model" 
            :placeholder="placeholder"
            :autofocus="autofocus"
        />
        <InputError class="mt-1" :message="error" />
    </div>
</template>