<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { Category } from '@/types/models';

const { t } = useI18n();

const props = defineProps<{
    modelValue: string | number;
    categories: Category[];
    placeholder?: string;
    subcategoryPlaceholder?: string;
    showAllOption?: boolean;
    required?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const selectedParentId = ref<string | number>('');
const selectedChildId  = ref<string | number>('');

function initFromValue(val: string | number) {
    if (val === '' || val === null || val === undefined) {
        selectedParentId.value = '';
        selectedChildId.value  = '';
        return;
    }

    const numVal = Number(val);

    // Is it a top-level category?
    if (props.categories.some(c => c.id === numVal)) {
        selectedParentId.value = numVal;
        selectedChildId.value  = '';
        return;
    }

    // Is it a child category?
    for (const parent of props.categories) {
        if (parent.children?.some(c => c.id === numVal)) {
            selectedParentId.value = parent.id;
            selectedChildId.value  = numVal;
            return;
        }
    }
}

watch(() => props.modelValue, initFromValue, { immediate: true });

const selectedParent = computed(() =>
    props.categories.find(c => c.id === Number(selectedParentId.value)) ?? null
);

const hasChildren = computed(() => (selectedParent.value?.children?.length ?? 0) > 0);

const selectClass = 'border-gray-300 focus:border-navy focus:ring-navy rounded-md shadow-sm w-full text-sm';

function onParentChange(event: Event) {
    const val = (event.target as HTMLSelectElement).value;
    selectedParentId.value = val;
    selectedChildId.value  = '';

    if (!val) {
        emit('update:modelValue', '');
        return;
    }

    const parent = props.categories.find(c => c.id === Number(val));

    if (!parent?.children?.length) {
        // Leaf parent - emit immediately
        emit('update:modelValue', Number(val));
    } else {
        // Has children - emit parent ID so filter shows parent + children results
        emit('update:modelValue', Number(val));
    }
}

function onChildChange(event: Event) {
    const val = (event.target as HTMLSelectElement).value;
    selectedChildId.value = val;
    emit('update:modelValue', val ? Number(val) : '');
}
</script>

<template>
    <div>
        <select
            :value="selectedParentId"
            @change="onParentChange"
            :class="selectClass"
            :required="required && !hasChildren"
        >
            <option v-if="showAllOption !== false" value="">{{ placeholder ?? t('forms.category_select.all_categories') }}</option>
            <option v-else value="" disabled>{{ placeholder ?? t('forms.category_select.select_category') }}</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>

        <select
            v-if="hasChildren"
            :value="selectedChildId"
            @change="onChildChange"
            :class="selectClass + ' mt-2'"
            :required="required"
        >
            <option value="" :disabled="required">
                {{ subcategoryPlaceholder ?? t('forms.category_select.select_subcategory') }}
            </option>
            <option v-for="child in selectedParent?.children" :key="child.id" :value="child.id">
                {{ child.name }}
            </option>
        </select>
    </div>
</template>
