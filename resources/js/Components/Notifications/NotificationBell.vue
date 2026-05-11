<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from '@inertiajs/vue3';
import { useNotifications } from '@/composables/useNotifications';
import { useEscapeKey } from '@/composables/useEscapeKey';
import type { Notification, NotificationType } from '@/types/notification';
import {
    BellIcon,
    BriefcaseIcon,
    CheckCircleIcon,
    XCircleIcon,
    ChatBubbleLeftIcon,
    CheckBadgeIcon,
    StarIcon,
    TrashIcon,
    CurrencyEuroIcon,
    ShieldExclamationIcon,
    ArrowPathIcon,
    TagIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{ userId: number }>();

const {
    notifications,
    unreadCount,
    loading,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    subscribeToRealtime,
    unsubscribe,
} = useNotifications();

const open = ref(false);
const container = ref<HTMLElement | null>(null);
const menuRef = ref<HTMLElement | null>(null);

useEscapeKey(() => { open.value = false; }, () => open.value);

const iconMap: Record<NotificationType, any> = {
    new_application:      BriefcaseIcon,
    application_accepted: CheckCircleIcon,
    application_rejected: XCircleIcon,
    new_message:          ChatBubbleLeftIcon,
    job_completed:        CheckBadgeIcon,
    new_review:           StarIcon,
    job_paid:             CurrencyEuroIcon,
    job_marked_complete:  CheckBadgeIcon,
    job_confirmed:        CheckCircleIcon,
    job_disputed:         ShieldExclamationIcon,
    job_cancelled:          XCircleIcon,
    job_auto_released:      ArrowPathIcon,
    application_shortlisted: StarIcon,
    proposal_received:              CurrencyEuroIcon,
    proposal_accepted:              CurrencyEuroIcon,
    proposal_rejected:              CurrencyEuroIcon,
    proposal_withdrawn:             CurrencyEuroIcon,
    new_category_suggestion:        TagIcon,
    category_suggestion_approved:   TagIcon,
    category_suggestion_rejected:   TagIcon,
    category_suggestion_merged:     TagIcon,
};

function formatTime(iso: string): string {
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
    if (diff < 60) return 'tikko';
    if (diff < 3600) return `pirms ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `pirms ${Math.floor(diff / 3600)} h`;
    if (diff < 172800) return 'vakar';
    return `pirms ${Math.floor(diff / 86400)} d`;
}

async function handleClick(n: Notification) {
    if (!n.is_read) await markAsRead(n.id);
    if (n.action_url) window.location.href = n.action_url;
    open.value = false;
}

async function handleDelete(e: Event, id: number) {
    e.stopPropagation();
    await deleteNotification(id);
}

function handleOutsideClick(e: MouseEvent) {
    if (container.value && !container.value.contains(e.target as Node)) {
        open.value = false;
    }
}

function handleMenuKeydown(e: KeyboardEvent) {
    if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
    e.preventDefault();
    const items = Array.from(
        menuRef.value?.querySelectorAll<HTMLElement>('[role="menuitem"]') ?? []
    );
    if (items.length === 0) return;
    const idx = items.indexOf(document.activeElement as HTMLElement);
    if (e.key === 'ArrowDown') items[(idx + 1) % items.length]?.focus();
    if (e.key === 'ArrowUp') items[(idx - 1 + items.length) % items.length]?.focus();
}

onMounted(() => {
    fetchNotifications();
    subscribeToRealtime(props.userId);
    document.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
    unsubscribe(props.userId);
    document.removeEventListener('click', handleOutsideClick);
});
</script>

<template>
    <div ref="container" class="relative">
        <!-- Bell button -->
        <button
            @click="open = !open"
            :aria-expanded="open"
            aria-haspopup="menu"
            aria-controls="notification-dropdown"
            :aria-label="unreadCount > 0 ? `Paziņojumi (${unreadCount} nelasīti)` : 'Paziņojumi'"
            class="relative inline-flex items-center justify-center w-8 h-8 rounded-md text-white/60 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-2 focus-visible:ring-offset-navy"
        >
            <BellIcon class="w-5 h-5" aria-hidden="true" />
            <span
                v-if="unreadCount > 0"
                aria-hidden="true"
                class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full leading-none"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div
            v-if="open"
            id="notification-dropdown"
            ref="menuRef"
            role="menu"
            aria-label="Paziņojumi"
            @keydown="handleMenuKeydown"
            class="absolute right-0 top-full mt-2 w-96 max-w-[calc(100vw-1rem)] bg-white border border-navy/10 rounded-xl shadow-xl z-50 overflow-hidden"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <span class="text-sm font-bold text-navy">Paziņojumi</span>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-navy/60 hover:text-navy transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy rounded"
                >
                    Atzīmēt visus kā lasītus
                </button>
            </div>

            <!-- List -->
            <div class="max-h-[400px] overflow-y-auto divide-y divide-gray-50">
                <div
                    v-if="loading && notifications.length === 0"
                    class="py-8 text-center text-sm text-gray-400"
                    role="status"
                >
                    Ielādē...
                </div>

                <div
                    v-else-if="notifications.length === 0"
                    class="py-10 text-center"
                >
                    <BellIcon class="w-8 h-8 text-gray-200 mx-auto mb-2" aria-hidden="true" />
                    <p class="text-sm text-gray-400">Nav jaunu paziņojumu</p>
                </div>

                <div
                    v-for="n in notifications"
                    :key="n.id"
                    role="menuitem"
                    tabindex="0"
                    @click="handleClick(n)"
                    @keydown.enter="handleClick(n)"
                    @keydown.space.prevent="handleClick(n)"
                    class="group relative flex gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors focus:outline-none focus-visible:bg-slate-50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-navy"
                    :class="!n.is_read ? 'border-l-2 border-navy bg-navy/[0.02]' : 'border-l-2 border-transparent'"
                >
                    <!-- Icon -->
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                        :class="!n.is_read ? 'bg-navy/10' : 'bg-gray-100'"
                        aria-hidden="true"
                    >
                        <component
                            :is="iconMap[n.type]"
                            class="w-4 h-4"
                            :class="!n.is_read ? 'text-navy' : 'text-gray-400'"
                        />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 leading-tight" :class="!n.is_read ? 'text-navy' : ''">
                            {{ n.title }}
                        </p>
                        <p v-if="n.body" class="text-xs text-gray-500 mt-0.5 leading-relaxed line-clamp-2">
                            {{ n.body }}
                        </p>
                        <p class="text-[10px] text-gray-400 mt-1">{{ formatTime(n.created_at) }}</p>
                    </div>

                    <!-- Delete -->
                    <button
                        @click="handleDelete($event, n.id)"
                        aria-label="Dzēst paziņojumu"
                        tabindex="0"
                        class="absolute right-3 top-3 opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-red-50 text-gray-300 hover:text-red-400 transition-all focus:opacity-100 focus-visible:ring-2 focus-visible:ring-red-400"
                    >
                        <TrashIcon class="w-3.5 h-3.5" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
