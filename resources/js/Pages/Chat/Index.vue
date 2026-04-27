<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';

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
}

const props = defineProps<{
    conversations: Conversation[];
}>();

const formatTime = (iso: string) => {
    const d = new Date(iso);
    const now = new Date();
    const isToday = d.toDateString() === now.toDateString();
    if (isToday) {
        return d.toLocaleTimeString('lv-LV', { hour: '2-digit', minute: '2-digit' });
    }
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

            <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden divide-y divide-gray-50">
                <Link
                    v-for="conv in conversations"
                    :key="conv.id"
                    :href="route('chat.show', conv.id)"
                    class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50/60 transition-colors"
                >
                    <div class="w-11 h-11 rounded-full bg-navy flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ initials(conv.other_user.name) }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ conv.other_user.name }}</p>
                            <span v-if="conv.last_message" class="text-xs text-gray-400 flex-shrink-0">
                                {{ formatTime(conv.last_message.created_at) }}
                            </span>
                        </div>
                        <p v-if="conv.last_message" class="text-xs text-gray-500 truncate mt-0.5">
                            {{ conv.last_message.body }}
                        </p>
                        <p v-else class="text-xs text-gray-400 italic mt-0.5">Nav ziņojumu</p>
                    </div>
                </Link>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
