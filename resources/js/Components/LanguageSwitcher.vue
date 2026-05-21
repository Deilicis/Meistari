<script setup lang="ts">
import { useLocale } from '@/composables/useLocale';

const { locale, setLocale } = useLocale();
</script>

<template>
    <div class="flex items-center gap-1">
        <button
            type="button"
            :class="[
                'px-2 py-0.5 text-xs font-semibold rounded transition-colors text-white',
                locale === 'lv'
                    ? 'bg-gold text-navy'
                    : 'text-current opacity-60 hover:opacity-100',
            ]"
            @click="setLocale('lv')"
        >
            LV
        </button>
        <span class="opacity-30 text-xs">|</span>
        <button
            type="button"
            :class="[
                'px-2 py-0.5 text-xs font-semibold rounded transition-colors text-white',
                locale === 'en'
                    ? 'bg-gold text-navy'
                    : 'text-current opacity-60 hover:opacity-100',
            ]"
            @click="setLocale('en')"
        >
            EN
        </button>
    </div>
</template>
