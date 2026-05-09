<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ChatBubbleLeftRightIcon, MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface LastMessage {
    id: number;
    body: string;
    sender_id: number;
    created_at: string;
}

interface OtherUser {
    id: number;
    name: string;
}

interface Conversation {
    id: number;
    other_user: OtherUser;
    last_message: LastMessage | null;
    created_at: string;
    unread_count: number;
}

const props = defineProps<{
    conversations: Conversation[];
}>();

const search = ref('');

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.conversations;
    return props.conversations.filter(c => c.other_user.name.toLowerCase().includes(q));
});

const formatTime = (iso: string): string => {
    const d = new Date(iso);
    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'Tikko';
    if (diffMins < 60) return `pirms ${diffMins} min`;

    const isToday = d.toDateString() === now.toDateString();
    if (isToday) return d.toLocaleTimeString('lv-LV', { hour: '2-digit', minute: '2-digit' });

    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    if (d.toDateString() === yesterday.toDateString()) return 'Vakar';

    return d.toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit' });
};

const initials = (name: string) => name.slice(0, 2).toUpperCase();
</script>

<template>
    <Head title="Ziņojumi" />

    <AuthenticatedLayout>
        <div class="bg-navy">
            <div class="h-1 bg-blue-400" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center gap-3">
                    <ChatBubbleLeftRightIcon class="w-6 h-6 text-blue-400" />
                    <div>
                        <h1 class="text-2xl font-extrabold text-white tracking-tight">Ziņojumi</h1>
                        <p class="text-white/50 text-sm mt-0.5">
                            <span class="text-blue-400 font-semibold">{{ conversations.length }}</span>
                            sarunā{{ conversations.length === 1 ? '' : 's' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">

            <div v-if="conversations.length === 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <ChatBubbleLeftRightIcon class="w-12 h-12 text-gray-200 mx-auto mb-4" />
                <p class="text-gray-400 text-sm">Nav nevienas sarunas.</p>
                <p class="text-gray-300 text-xs mt-1">Sāc sarunas, apmeklējot meistara profilu.</p>
            </div>

            <template v-else>
                <!-- Search -->
                <div class="relative mb-4">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Meklēt sarunu pēc vārda..."
                        aria-label="Meklēt sarunu"
                        class="w-full pl-9 pr-9 py-2.5 text-sm rounded-xl border border-gray-200 focus:border-navy focus:ring-1 focus:ring-navy outline-none bg-white shadow-sm"
                    />
                    <button
                        v-if="search"
                        @click="search = ''"
                        aria-label="Notīrīt meklēšanu"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <XMarkIcon class="w-4 h-4" />
                    </button>
                </div>

                <!-- No search results -->
                <div v-if="filtered.length === 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
                    <p class="text-gray-500 text-sm">Nav atrastu sarunu ar vārdu <span class="font-semibold">„{{ search.trim() }}"</span>.</p>
                    <button
                        @click="search = ''"
                        class="mt-3 text-sm font-medium text-navy hover:underline"
                    >
                        Notīrīt meklēšanu
                    </button>
                </div>

                <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden divide-y divide-gray-50">
                    <Link
                        v-for="conv in filtered"
                        :key="conv.id"
                        :href="route('chat.show', conv.id)"
                        class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50/60 transition-colors"
                    >
                        <div class="relative flex-shrink-0">
                            <div class="w-11 h-11 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm">
                                {{ initials(conv.other_user.name) }}
                            </div>
                            <span
                                v-if="conv.unread_count > 0"
                                class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center text-[9px] font-bold text-white"
                            >
                                {{ conv.unread_count > 9 ? '9+' : conv.unread_count }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between gap-2">
                                <p
                                    class="text-sm truncate"
                                    :class="conv.unread_count > 0 ? 'font-bold text-gray-900' : 'font-semibold text-gray-900'"
                                >
                                    {{ conv.other_user.name }}
                                </p>
                                <span v-if="conv.last_message" class="text-xs text-gray-400 flex-shrink-0">
                                    {{ formatTime(conv.last_message.created_at) }}
                                </span>
                            </div>
                            <p
                                v-if="conv.last_message"
                                class="text-xs truncate mt-0.5"
                                :class="conv.unread_count > 0 ? 'font-semibold text-gray-700' : 'text-gray-500'"
                            >
                                {{ conv.last_message.body || 'Cenas piedāvājums' }}
                            </p>
                            <p v-else class="text-xs text-gray-400 italic mt-0.5">Nav ziņojumu</p>
                        </div>
                    </Link>
                </div>
            </template>

        </div>
    </AuthenticatedLayout>
</template>
