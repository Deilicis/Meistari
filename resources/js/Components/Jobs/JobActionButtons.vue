<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import type { JobLifecycle, JobAllowedAction } from '@/types/jobLifecycle';
import ConfirmDisputeModal from '@/Components/Jobs/ConfirmDisputeModal.vue';
import ConfirmCancelModal from '@/Components/Jobs/ConfirmCancelModal.vue';

const props = defineProps<{ job: JobLifecycle }>();
const emit = defineEmits<{ updated: [job: JobLifecycle] }>();

const loading = ref<JobAllowedAction | null>(null);
const showDisputeModal = ref(false);
const showCancelModal = ref(false);

const actionLabels: Partial<Record<JobAllowedAction, string>> = {
    pay:           'Apmaksāt darbu',
    mark_complete: 'Atzīmēt kā pabeigtu',
    confirm:       'Apstiprināt pabeigšanu',
    dispute:       'Atvērt strīdu',
    cancel:        'Atcelt darbu',
};

const routeMap: Partial<Record<JobAllowedAction, () => string>> = {
    pay:           () => route('jobs.lifecycle.pay',          props.job.id),
    mark_complete: () => route('jobs.lifecycle.mark-complete', props.job.id),
    confirm:       () => route('jobs.lifecycle.confirm',      props.job.id),
    dispute:       () => route('jobs.lifecycle.dispute',      props.job.id),
    cancel:        () => route('jobs.lifecycle.cancel',       props.job.id),
};

async function post(action: JobAllowedAction, body: Record<string, unknown> = {}) {
    if (loading.value) return;
    const url = routeMap[action]?.();
    if (!url) return;
    loading.value = action;
    try {
        const { data } = await axios.post(url, body);
        if (action === 'pay' && data.url) {
            window.location.href = data.url;
            return;
        }
        emit('updated', data);
        toast.success('Veiksmīgi!');
    } catch (e: any) {
        toast.error(e?.response?.data?.message ?? 'Radās kļūda.');
    } finally {
        loading.value = null;
    }
}

function handleAction(action: JobAllowedAction) {
    if (action === 'dispute') { showDisputeModal.value = true; return; }
    if (action === 'cancel')  { showCancelModal.value = true; return; }
    post(action);
}

function onDisputeSubmit(reason: string) {
    showDisputeModal.value = false;
    post('dispute', { reason });
}

function onCancelSubmit(reason: string | null) {
    showCancelModal.value = false;
    post('cancel', { reason: reason ?? undefined });
}
</script>

<template>
    <div v-if="job.allowed_actions.filter(a => a !== 'accept_application').length > 0" class="flex flex-wrap gap-2">
        <template v-for="action in job.allowed_actions" :key="action">
            <button
                v-if="action !== 'accept_application'"
                @click="handleAction(action)"
                :disabled="!!loading"
                class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :class="{
                    'bg-emerald-500 hover:bg-emerald-600 text-white': action === 'confirm',
                    'bg-navy hover:bg-navy/90 text-white':            action === 'pay' || action === 'mark_complete',
                    'bg-red-500 hover:bg-red-600 text-white':         action === 'dispute',
                    'bg-gray-500 hover:bg-gray-600 text-white':       action === 'cancel',
                }"
            >
                {{ loading === action ? 'Lūdzu, uzgaidiet...' : actionLabels[action] }}
            </button>
        </template>
    </div>

    <ConfirmDisputeModal
        :show="showDisputeModal"
        :job-id="job.id"
        @close="showDisputeModal = false"
        @submitted="onDisputeSubmit"
    />
    <ConfirmCancelModal
        :show="showCancelModal"
        @close="showCancelModal = false"
        @submitted="onCancelSubmit"
    />
</template>
