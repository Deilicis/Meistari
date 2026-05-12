<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { XMarkIcon } from '@heroicons/vue/24/outline';

defineProps<{
    show: boolean;
    canRegister?: boolean;
}>();

const emit = defineEmits<{ close: [] }>();
const { t } = useI18n();
</script>

<template>
    <Transition
        enter-active-class="ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50" @click="emit('close')" />
            <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center">
                <button @click="emit('close')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <XMarkIcon class="w-5 h-5" />
                </button>
                <div class="w-12 h-12 rounded-xl bg-navy/10 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-navy mb-2">{{ t('modals.guest.title') }}</h3>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    {{ t('modals.guest.desc') }}
                </p>
                <div class="flex gap-3">
                    <Link :href="route('login')"
                        class="flex-1 py-2.5 rounded-xl border border-navy text-navy text-sm font-semibold hover:bg-navy/5 transition-colors text-center">
                        {{ t('modals.guest.login') }}
                    </Link>
                    <Link v-if="canRegister" :href="route('register')"
                        class="flex-1 py-2.5 rounded-xl bg-navy text-white text-sm font-bold hover:bg-navy-hover transition-colors text-center">
                        {{ t('modals.guest.register') }}
                    </Link>
                </div>
            </div>
        </div>
    </Transition>
</template>
