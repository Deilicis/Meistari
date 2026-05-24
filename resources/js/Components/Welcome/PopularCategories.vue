<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { resolveIcon } from '@/utils/heroicons';
import type { PopularCategory } from '@/types/welcome';

const { t } = useI18n();

defineProps<{
    categories: PopularCategory[];
    isAuth: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <section class="py-16 bg-slate-50 border-t border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-navy">{{ t('welcome.categories.title') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ t('welcome.categories.subtitle') }}</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <component
                    :is="isAuth ? Link : 'button'"
                    v-for="cat in categories"
                    :key="cat.id"
                    v-bind="isAuth ? { href: route('seeker.categories.index') } : {}"
                    @click="!isAuth ? $router?.push(route('register')) : undefined"
                    class="group bg-white rounded-xl border border-slate-200 hover:border-navy/30 hover:shadow-md hover:-translate-y-0.5 p-6 flex items-center gap-4 transition-all text-left w-full"
                >
                    <div class="w-12 h-12 rounded-lg bg-navy/5 group-hover:bg-navy/10 flex items-center justify-center flex-shrink-0 transition-colors">
                        <component
                            :is="resolveIcon(cat.icon)"
                            class="w-6 h-6 text-navy/60 group-hover:text-navy transition-colors"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="min-w-0">
                        <p class="text-base font-semibold text-gray-900 truncate">{{ cat.name }}</p>
                        <p class="text-sm text-gray-400 mt-0.5">
                            {{ cat.services_count }} {{ t('welcome.categories.services_label', cat.services_count) }}
                        </p>
                    </div>
                </component>
            </div>

            <div v-if="!isAuth && canRegister" class="text-center mt-8">
                <Link :href="route('register')" class="text-sm font-semibold text-navy hover:underline">
                    {{ t('welcome.categories.register_to_browse') }}
                </Link>
            </div>
        </div>
    </section>
</template>
