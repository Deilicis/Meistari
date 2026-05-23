<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    UserCircleIcon,
    BriefcaseIcon,
    ShieldCheckIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

const { t } = useI18n();

const props = defineProps<{
    activeTab: string;
    isMaster: boolean;
}>();

const emit = defineEmits<{
    'update:activeTab': [tab: string];
}>();

interface Tab {
    key: string;
    labelKey: string;
    icon: any;
}

const allTabs = computed((): Tab[] => [
    { key: 'basic', labelKey: 'profile.tabs.basic_info', icon: UserCircleIcon },
    ...(props.isMaster
        ? [{ key: 'master-profile', labelKey: 'profile.tabs.master_profile', icon: BriefcaseIcon }]
        : []),
    { key: 'security', labelKey: 'profile.tabs.security', icon: ShieldCheckIcon },
    { key: 'account', labelKey: 'profile.tabs.account', icon: TrashIcon },
]);

const tabRefs = ref<HTMLButtonElement[]>([]);

function onKeydown(e: KeyboardEvent, index: number) {
    const tabs = allTabs.value;
    let newIndex: number | null = null;

    if (e.key === 'ArrowRight') newIndex = (index + 1) % tabs.length;
    else if (e.key === 'ArrowLeft') newIndex = (index - 1 + tabs.length) % tabs.length;
    else if (e.key === 'Home') newIndex = 0;
    else if (e.key === 'End') newIndex = tabs.length - 1;
    else return;

    e.preventDefault();
    emit('update:activeTab', tabs[newIndex].key);
    tabRefs.value[newIndex]?.focus();
}
</script>

<template>
    <div
        role="tablist"
        :aria-label="t('profile.heading')"
        class="flex items-end overflow-x-auto border-b border-gray-200 gap-1 scrollbar-hide"
    >
        <button
            v-for="(tab, i) in allTabs"
            :key="tab.key"
            ref="tabRefs"
            role="tab"
            :id="`tab-${tab.key}`"
            :aria-selected="activeTab === tab.key"
            :aria-controls="`tabpanel-${tab.key}`"
            :tabindex="activeTab === tab.key ? 0 : -1"
            type="button"
            @click="emit('update:activeTab', tab.key)"
            @keydown="onKeydown($event, i)"
            class="relative flex items-center gap-1.5 px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors border-b-2 -mb-px focus:outline-none focus-visible:ring-2 focus-visible:ring-navy focus-visible:ring-inset rounded-t-lg"
            :class="activeTab === tab.key
                ? 'text-navy border-navy'
                : 'text-gray-400 border-transparent hover:text-gray-600 hover:border-gray-300'"
        >
            <component :is="tab.icon" class="w-4 h-4 shrink-0" aria-hidden="true" />
            {{ t(tab.labelKey) }}
        </button>
    </div>
</template>
