<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { ShieldCheckIcon } from '@heroicons/vue/24/outline';
import UpdatePasswordForm from './UpdatePasswordForm.vue';

const { t } = useI18n();
</script>

<template>
    <div
        role="tabpanel"
        id="tabpanel-security"
        aria-labelledby="tab-security"
        class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden"
    >
        <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
            <ShieldCheckIcon class="w-5 h-5 text-gray-400 shrink-0" aria-hidden="true" />
            <div>
                <h3 class="text-base font-semibold text-gray-800">{{ t('profile.change_password_title') }}</h3>
                <p class="text-sm text-gray-400 mt-0.5">{{ t('profile.change_password_desc') }}</p>
            </div>
        </div>
        <div class="p-6">
            <UpdatePasswordForm />
        </div>
    </div>
</template>
