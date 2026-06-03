<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useActiveRole } from '@/composables/useActiveRole';
import { toast } from 'vue-sonner';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { UserCircleIcon } from '@heroicons/vue/24/outline';
import ProfileTabs from './Partials/ProfileTabs.vue';
import BasicInformationTab from './Partials/BasicInformationTab.vue';
import MasterProfileTab from './Partials/MasterProfileTab.vue';
import SecurityTab from './Partials/SecurityTab.vue';
import AccountTab from './Partials/AccountTab.vue';
import type { AuthUser, Profile } from '@/types/models';

const { t } = useI18n();
const { isMasterActive } = useActiveRole();

type ProfileUser = AuthUser & {
    profile: Profile & {
        experiences: object[];
        portfolio_images: string[];
        reg_number?: string;
        vat_number?: string;
    };
};

const page = usePage<{ auth: { user: ProfileUser } }>();
const user = computed(() => page.props.auth.user);
const profile = computed(() => user.value.profile || {});

defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
}>();

// --- Cilņu stāvoklis ---

type TabKey = 'basic' | 'master-profile' | 'security' | 'account';

const validTabs = computed((): TabKey[] => {
    const tabs: TabKey[] = ['basic'];
    if (isMasterActive.value) tabs.push('master-profile');
    tabs.push('security', 'account');
    return tabs;
});

const activeTab = ref<TabKey>('basic');

onMounted(() => {
    const hash = window.location.hash.replace('#', '') as TabKey;
    if (hash && validTabs.value.includes(hash)) {
        activeTab.value = hash;
    }
});

watch(activeTab, (tab) => {
    history.replaceState(null, '', `#${tab}`);
});

// Pagriezies uz 'basic', ja aktīvā loma mainās un pašreizējā cilne vairs nav pieejama.
watch(isMasterActive, () => {
    if (activeTab.value === 'master-profile' && !isMasterActive.value) {
        activeTab.value = 'basic';
    }
});

// --- Profila forma (kopīga starp 'basic' un 'master-profile' cilnēm) ---

const existingPortfolio = ref<string[]>(profile.value.portfolio_images || []);

watch(
    () => profile.value.portfolio_images,
    (newImages) => { existingPortfolio.value = newImages || []; },
    { deep: true }
);

const form = useForm({
    _method: 'patch',
    name: user.value.name,
    email: user.value.email,
    type: profile.value.type || 'individual',
    first_name: profile.value.first_name || '',
    last_name: profile.value.last_name || '',
    company_name: profile.value.company_name || '',
    reg_number: profile.value.reg_number || '',
    vat_number: profile.value.vat_number || '',
    city: profile.value.city || '',
    phone_number: profile.value.phone || '',
    description: profile.value.bio || '',
    avatar: null as File | string | null,
    experiences: profile.value.experiences || [],
    portfolio_images: [] as File[],
    images_to_delete: [] as string[],
});

function submitProfile() {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.portfolio_images = [];
            form.images_to_delete = [];
            form.avatar = null;
        },
        onError: () => toast.error(t('profile.form_error_toast')),
    });
}
</script>

<template>
    <Head :title="t('profile.title')" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1" :class="isMasterActive ? 'bg-gold' : 'bg-emerald-400'" />
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <UserCircleIcon class="w-6 h-6" :class="isMasterActive ? 'text-gold' : 'text-emerald-400'" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ t('profile.heading') }}</h1>
                        <p class="text-white/50 text-sm mt-0.5">{{ t('profile.subtitle') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-4">

                <ProfileTabs
                    v-model:activeTab="activeTab"
                    :isMaster="isMasterActive"
                />

                <Transition name="tab-fade" mode="out-in">
                    <BasicInformationTab
                        v-if="activeTab === 'basic'"
                        key="basic"
                        :form="form"
                        @submit="submitProfile"
                    />

                    <MasterProfileTab
                        v-else-if="activeTab === 'master-profile' && isMasterActive"
                        key="master-profile"
                        :form="form"
                        v-model:existingPortfolio="existingPortfolio"
                        @submit="submitProfile"
                    />

                    <SecurityTab
                        v-else-if="activeTab === 'security'"
                        key="security"
                    />

                    <AccountTab
                        v-else-if="activeTab === 'account'"
                        key="account"
                    />
                </Transition>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.tab-fade-enter-active,
.tab-fade-leave-active {
    transition: opacity 150ms ease;
}
.tab-fade-enter-from,
.tab-fade-leave-to {
    opacity: 0;
}
</style>
