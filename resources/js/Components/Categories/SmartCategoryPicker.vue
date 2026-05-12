<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import {
    CheckIcon,
    ClockIcon,
    SparklesIcon,
    XMarkIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import type { PickerSelection, CategorySearchResult } from '@/types/categorysuggestion';

const props = defineProps<{
    modelValue: PickerSelection | null;
    parentCategoryId?: number | null;
    placeholder?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: PickerSelection | null];
}>();

const { t } = useI18n();

const containerRef = ref<HTMLElement | null>(null);
const query        = ref('');
const showDropdown = ref(false);
const results      = ref<CategorySearchResult>({ approved: [], pending: [] });
const loading      = ref(false);

const showSuggestForm    = ref(false);
const suggestionName     = ref('');
const suggestionNote     = ref('');
const submittingSuggestion = ref(false);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const hasPendingSuggestion = (sel: PickerSelection | null): sel is { type: 'suggestion'; id: number; name: string } =>
    sel?.type === 'suggestion';

function triggerSearch(term: string) {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchResults(term), 300);
}

async function fetchResults(term: string) {
    if (!term.trim()) {
        results.value = { approved: [], pending: [] };
        return;
    }
    loading.value = true;
    try {
        const params: Record<string, string | number> = { q: term };
        if (props.parentCategoryId != null) params.parent_id = props.parentCategoryId;
        const res = await axios.get(route('api.categories.search'), { params });
        results.value = res.data;
    } catch {
        results.value = { approved: [], pending: [] };
    } finally {
        loading.value = false;
    }
}

watch(query, (val) => {
    if (props.disabled) return;
    showDropdown.value = val.trim().length > 0;
    triggerSearch(val);
});

function openDropdown() {
    if (props.disabled) return;
    if (query.value.trim()) showDropdown.value = true;
}

function selectApproved(id: number, name: string) {
    emit('update:modelValue', { type: 'category', id, name });
    query.value = name;
    showDropdown.value = false;
    showSuggestForm.value = false;
}

function attachPending(id: number, name: string) {
    emit('update:modelValue', { type: 'suggestion', id, name });
    query.value = '';
    showDropdown.value = false;
    showSuggestForm.value = false;
}

function openSuggestForm() {
    suggestionName.value = query.value.trim();
    suggestionNote.value = '';
    showSuggestForm.value = true;
    showDropdown.value = false;
}

function cancelSuggest() {
    showSuggestForm.value = false;
    showDropdown.value = query.value.trim().length > 0;
}

async function submitSuggestion() {
    if (!suggestionName.value.trim()) return;
    submittingSuggestion.value = true;
    try {
        const res = await axios.post(route('api.category-suggestions.store'), {
            name: suggestionName.value.trim(),
            parent_category_id: props.parentCategoryId ?? null,
            note: suggestionNote.value.trim() || null,
        });
        const suggestion = res.data;
        emit('update:modelValue', { type: 'suggestion', id: suggestion.id, name: suggestion.name });
        query.value = '';
        showSuggestForm.value = false;
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? t('categories.picker.suggest_error'));
    } finally {
        submittingSuggestion.value = false;
    }
}

function clearSelection() {
    emit('update:modelValue', null);
    query.value = '';
    showDropdown.value = false;
    showSuggestForm.value = false;
}

function handleOutsideClick(e: MouseEvent) {
    if (containerRef.value && !containerRef.value.contains(e.target as Node)) {
        showDropdown.value = false;
        showSuggestForm.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick));
onBeforeUnmount(() => document.removeEventListener('mousedown', handleOutsideClick));

const hasResults = () =>
    results.value.approved.length > 0 || results.value.pending.length > 0;

const showSuggestAction = () =>
    query.value.trim().length >= 3 && !showSuggestForm.value;

// Keep input in sync when external modelValue changes to a category (e.g., editing)
watch(() => props.modelValue, (val) => {
    if (val?.type === 'category' && query.value !== val.name) {
        query.value = val.name;
    } else if (val === null && !showSuggestForm.value) {
        query.value = '';
    }
}, { immediate: true });
</script>

<template>
    <div ref="containerRef" class="relative">
        <!-- Pending suggestion pill -->
        <template v-if="hasPendingSuggestion(modelValue)">
            <div class="flex flex-col gap-1 p-3 rounded-xl border border-amber-200 bg-amber-50">
                <div class="flex items-center gap-2">
                    <ClockIcon class="w-4 h-4 text-amber-600 flex-shrink-0" />
                    <span class="text-sm font-medium text-amber-800 truncate min-w-0">
                        {{ t('categories.picker.pending_prefix', { name: modelValue.name }) }}
                    </span>
                    <button
                        type="button"
                        @click="clearSelection"
                        class="ml-auto p-0.5 text-amber-500 hover:text-amber-700 flex-shrink-0"
                        :aria-label="t('categories.picker.pending_remove_aria')"
                    >
                        <XMarkIcon class="w-4 h-4" />
                    </button>
                </div>
                <p class="text-xs text-amber-600 leading-snug">
                    {{ t('categories.picker.pending_helper') }}
                </p>
            </div>
        </template>

        <!-- Normal typeahead input -->
        <template v-else>
            <div class="relative">
                <MagnifyingGlassIcon
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                />
                <input
                    v-model="query"
                    type="text"
                    :placeholder="placeholder ?? t('categories.picker.placeholder')"
                    :disabled="disabled"
                    @focus="openDropdown"
                    class="w-full pl-9 pr-8 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:border-navy focus:ring-navy disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                    autocomplete="off"
                />
                <button
                    v-if="query && !disabled"
                    type="button"
                    @click="clearSelection"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    :aria-label="t('categories.picker.clear_aria')"
                >
                    <XMarkIcon class="w-4 h-4" />
                </button>
            </div>

            <!-- Dropdown -->
            <div
                v-if="showDropdown"
                class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
            >
                <!-- Loading -->
                <div v-if="loading" class="px-3 py-2 text-xs text-gray-400">{{ t('categories.picker.searching') }}</div>

                <template v-else>
                    <!-- Approved categories -->
                    <template v-if="results.approved.length > 0">
                        <div class="px-3 pt-2 pb-0.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                            {{ t('categories.picker.approved_section') }}
                        </div>
                        <button
                            v-for="cat in results.approved"
                            :key="'cat-' + cat.id"
                            type="button"
                            @click="selectApproved(cat.id, cat.name)"
                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left"
                        >
                            <CheckIcon class="w-3.5 h-3.5 text-green-500 flex-shrink-0" />
                            {{ cat.name }}
                        </button>
                    </template>

                    <!-- Pending suggestions -->
                    <template v-if="results.pending.length > 0">
                        <div class="px-3 pt-2 pb-0.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                            {{ t('categories.picker.pending_section') }}
                        </div>
                        <button
                            v-for="sug in results.pending"
                            :key="'sug-' + sug.id"
                            type="button"
                            @click="attachPending(sug.id, sug.name)"
                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-amber-700 hover:bg-amber-50 text-left"
                        >
                            <ClockIcon class="w-3.5 h-3.5 text-amber-500 flex-shrink-0" />
                            <span class="flex-1 min-w-0 truncate">{{ sug.name }}</span>
                            <span class="text-[10px] text-amber-400 flex-shrink-0">{{ t('categories.picker.pending_section') }}</span>
                        </button>
                    </template>

                    <!-- No results message -->
                    <div
                        v-if="!hasResults() && query.trim().length > 0 && !showSuggestAction()"
                        class="px-3 py-2 text-sm text-gray-400"
                    >
                        {{ t('categories.picker.no_results') }}
                    </div>

                    <!-- Suggest action -->
                    <button
                        v-if="showSuggestAction()"
                        type="button"
                        @click="openSuggestForm"
                        class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-navy hover:bg-navy/5 border-t border-gray-100 text-left"
                    >
                        <SparklesIcon class="w-3.5 h-3.5 flex-shrink-0" />
                        {{ t('categories.picker.suggest_action', { name: query.trim() }) }}
                    </button>
                </template>
            </div>

            <!-- Inline suggestion form -->
            <div
                v-if="showSuggestForm"
                class="mt-2 p-4 border border-amber-200 bg-amber-50 rounded-xl space-y-3"
            >
                <p class="text-xs font-semibold text-amber-800">{{ t('categories.picker.suggest_title') }}</p>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ t('categories.picker.suggest_name_label') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="suggestionName"
                        type="text"
                        maxlength="100"
                        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:border-navy focus:ring-navy"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ t('categories.picker.suggest_note_label') }} <span class="text-gray-400">{{ t('categories.picker.suggest_note_optional') }}</span>
                    </label>
                    <textarea
                        v-model="suggestionNote"
                        maxlength="300"
                        rows="2"
                        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:border-navy focus:ring-navy resize-none"
                    />
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="submitSuggestion"
                        :disabled="!suggestionName.trim() || submittingSuggestion"
                        class="flex-1 px-3 py-1.5 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors disabled:opacity-40"
                    >
                        {{ submittingSuggestion ? t('categories.picker.suggest_submitting') : t('categories.picker.suggest_submit') }}
                    </button>
                    <button
                        type="button"
                        @click="cancelSuggest"
                        class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                        {{ t('common.cancel') }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
