<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ApplicationLogo from '@/Components/Common/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const { t } = useI18n();
</script>

<template>
    <footer class="bg-navy border-t border-white/5 py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <ApplicationLogo class="w-6 h-6 brightness-0 invert opacity-60" />
                <p class="text-sm text-white/30">&copy; 2026 Meistari</p>
            </div>
            <div class="flex items-center gap-6 text-sm text-white/30">
                <Link :href="route('noteikumi')" class="hover:text-white/60 transition-colors">{{ t('welcome.footer.terms') }}</Link>
                <Link :href="route('privatuma-politika')" class="hover:text-white/60 transition-colors">{{ t('welcome.footer.privacy') }}</Link>
                <Link :href="route('kontakti')" class="hover:text-white/60 transition-colors">{{ t('welcome.footer.contact') }}</Link>
                <LanguageSwitcher />
            </div>
        </div>
    </footer>
</template>
