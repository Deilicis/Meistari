<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import DeleteUserForm from './DeleteUserForm.vue';

const { t } = useI18n();
</script>

<template>
    <div
        role="tabpanel"
        id="tabpanel-account"
        aria-labelledby="tab-account"
        class="bg-white border border-red-100 rounded-2xl shadow-sm overflow-hidden"
    >
        <div class="bg-red-50 px-6 py-5 border-b border-red-100 flex items-center gap-3">
            <ExclamationTriangleIcon class="w-5 h-5 text-red-500 shrink-0" aria-hidden="true" />
            <div>
                <h3 class="text-base font-semibold text-red-700">{{ t('profile.danger_zone_title') }}</h3>
                <p class="text-sm text-red-400 mt-0.5">{{ t('profile.danger_zone_desc') }}</p>
            </div>
        </div>
        <div class="p-6">
            <DeleteUserForm />
        </div>
    </div>
</template>
