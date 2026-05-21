<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProposalChatCard from '@/Components/Chat/ProposalChatCard.vue';
import { PaperAirplaneIcon, ChatBubbleLeftRightIcon, MagnifyingGlassIcon, XMarkIcon, BriefcaseIcon } from '@heroicons/vue/24/outline';
import type { PriceProposal } from '@/types/proposal';

// --- Tipi ---
type MessageType = 'text' | 'proposal';

interface MessageSender {
    id: number;
    name: string;
}

interface Message {
    id: number;
    conversation_id: number;
    sender_id: number;
    body: string;
    type: MessageType;
    proposal_id: number | null;
    read_at: string | null;
    created_at: string;
    sender: MessageSender;
    proposal?: PriceProposal | null;
}

interface OtherUser {
    id: number;
    name: string;
}

interface Conversation {
    id: number;
    other_user: OtherUser;
    last_message: Message | null;
    created_at: string;
    unread_count?: number;
}

// --- Props ---
interface RelatedJob {
    id: number;
    title: string;
    status: string;
}

const props = defineProps<{
    conversation: Conversation;
    messages: Message[];
    conversations: Conversation[];
    auth_user_id: number;
    related_job: RelatedJob | null;
}>();

const { t } = useI18n();

// --- Stāvoklis ---
const messageList = ref<Message[]>([...props.messages]);
const newMessage = ref('');
const sending = ref(false);
const messagesEnd = ref<HTMLElement | null>(null);
const textarea = ref<HTMLTextAreaElement | null>(null);
const sidebarSearch = ref('');

const filteredConvs = computed(() => {
    const q = sidebarSearch.value.trim().toLowerCase();
    if (!q) return props.conversations;
    return props.conversations.filter(c => c.other_user.name.toLowerCase().includes(q));
});

// --- Palīgfunkcijas ---
const scrollToBottom = (smooth = true) => {
    nextTick(() => {
        messagesEnd.value?.scrollIntoView({ behavior: smooth ? 'smooth' : 'instant' });
    });
};

const formatTime = (iso: string) => {
    const d = new Date(iso);
    const now = new Date();
    const isToday = d.toDateString() === now.toDateString();
    if (isToday) {
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return d.toLocaleDateString([], { day: '2-digit', month: '2-digit' }) + ' ' +
        d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const initials = (name: string) => name.slice(0, 2).toUpperCase();

// --- Ziņojumu apstrāde ---
const sendMessage = async () => {
    const body = newMessage.value.trim();
    if (!body || sending.value) return;
    sending.value = true;
    newMessage.value = '';
    try {
        const res = await axios.post(route('chat.store', props.conversation.id), { body });
        const msg: Message = res.data;
        if (!messageList.value.find(m => m.id === msg.id)) {
            messageList.value.push(msg);
            scrollToBottom();
        }
    } catch {
        newMessage.value = body;
    } finally {
        sending.value = false;
        textarea.value?.focus();
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

// Kad tiek veikta darbība uz piedāvājuma kartes, atjaunina tā statusu
// visās kartēs, kas atsaucas uz to pašu proposal_id,
// un gaida reāllaika apraidi, kas pievienotu jauno notikuma karti.
function handleProposalActed(proposalId: number) {
    messageList.value.forEach(msg => {
        if (msg.proposal && msg.proposal.id === proposalId) {
            msg.proposal = { ...msg.proposal, is_pending: false };
        }
    });
}

// --- Reāllaikā ---
onMounted(() => {
    scrollToBottom(false);
    (window as any).Echo
        .private(`conversation.${props.conversation.id}`)
        .listen('.MessageSent', (e: Message) => {
            if (!messageList.value.find(m => m.id === e.id)) {
                messageList.value.push(e);
                scrollToBottom();
            }
            // Ja šis ir piedāvājuma notikums, atzīmē iepriekšējās kartes kā ne-gaidošas.
            if (e.type === 'proposal' && e.proposal_id) {
                messageList.value.forEach(msg => {
                    if (msg.proposal && msg.proposal.id === e.proposal_id && msg.id !== e.id) {
                        msg.proposal = { ...msg.proposal, is_pending: false };
                    }
                });
            }
        });
});

onUnmounted(() => {
    (window as any).Echo.leave(`conversation.${props.conversation.id}`);
});
</script>

<template>
    <Head :title="t('chat.conversation_title', { name: conversation.other_user.name })" />

    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-64px)]">

            
            <aside class="hidden md:flex flex-col w-72 bg-white border-r border-gray-100 flex-shrink-0">
                <div class="bg-navy px-4 py-3 flex-shrink-0">
                    <div class="h-0.5 bg-blue-400 -mx-4 -mt-3 mb-3" />
                    <Link :href="route('chat.index')" class="flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                        <ChatBubbleLeftRightIcon class="w-4 h-4 text-blue-400" />
                        <span class="text-sm font-semibold">{{ t('chat.title') }}</span>
                    </Link>
                </div>

                
                <div class="px-3 py-2 border-b border-gray-100 flex-shrink-0">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" />
                        <input
                            v-model="sidebarSearch"
                            type="text"
                            :placeholder="t('chat.search_placeholder')"
                            :aria-label="t('chat.search_aria')"
                            class="w-full pl-8 pr-7 py-1.5 text-xs rounded-lg border border-gray-200 focus:border-navy focus:ring-1 focus:ring-navy outline-none bg-gray-50"
                        />
                        <button
                            v-if="sidebarSearch"
                            @click="sidebarSearch = ''"
                            :aria-label="t('chat.clear_search_aria')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <XMarkIcon class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto flex-grow divide-y divide-gray-50">
                    <Link
                        v-for="conv in filteredConvs"
                        :key="conv.id"
                        :href="route('chat.show', conv.id)"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors"
                        :class="conv.id === conversation.id ? 'bg-blue-50 border-l-2 border-l-blue-400' : ''"
                    >
                        <div class="relative w-9 h-9 flex-shrink-0">
                            <div class="w-9 h-9 rounded-full bg-navy flex items-center justify-center text-white font-bold text-xs">
                                {{ initials(conv.other_user.name) }}
                            </div>
                            <span
                                v-if="conv.unread_count && conv.unread_count > 0"
                                class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-blue-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                            >{{ conv.unread_count > 9 ? '9+' : conv.unread_count }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate"
                               :class="conv.unread_count && conv.unread_count > 0 ? 'text-navy' : ''">
                                {{ conv.other_user.name }}
                            </p>
                            <p v-if="conv.last_message" class="text-xs text-gray-400 truncate"
                               :class="conv.unread_count && conv.unread_count > 0 ? 'font-semibold text-gray-600' : ''">
                                {{ conv.last_message.body || t('chat.price_proposal_fallback') }}
                            </p>
                        </div>
                    </Link>
                    <div v-if="filteredConvs.length === 0 && conversations.length > 0" class="p-4 text-xs text-gray-400 text-center">
                        <p>{{ t('chat.no_search_sidebar') }}</p>
                        <button @click="sidebarSearch = ''" class="mt-1 text-navy hover:underline">{{ t('chat.clear_sidebar') }}</button>
                    </div>
                    <div v-else-if="conversations.length === 0" class="p-4 text-xs text-gray-400 text-center">
                        {{ t('chat.no_conversations_sidebar') }}
                    </div>
                </div>
            </aside>

            
            <div class="flex flex-col flex-1 min-w-0">

                
                <div class="bg-white border-b border-gray-100 px-5 py-3 flex items-center gap-3 flex-shrink-0">
                    <Link :href="route('chat.index')" class="md:hidden text-gray-400 hover:text-navy transition-colors mr-1">
                        ←
                    </Link>
                    <div class="w-9 h-9 rounded-full bg-navy flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                        {{ initials(conversation.other_user.name) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ conversation.other_user.name }}</p>
                        <p class="text-xs text-gray-400">{{ t('chat.active_status') }}</p>
                    </div>
                </div>

                
                <div v-if="related_job" class="flex items-center gap-2 px-5 py-2 bg-navy/5 border-b border-navy/10 flex-shrink-0">
                    <BriefcaseIcon class="w-3.5 h-3.5 text-navy/50 flex-shrink-0" />
                    <span class="text-xs text-navy/70 truncate min-w-0">
                        {{ t('chat.related_job_banner') }} <span class="font-semibold">"{{ related_job.title }}"</span>
                    </span>
                    <a
                        :href="`/jobs/${related_job.id}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="ml-auto text-xs font-semibold text-navy hover:underline flex-shrink-0"
                    >{{ t('chat.view_job') }}</a>
                </div>

                
                <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50/40">
                    <div v-if="messageList.length === 0" class="flex items-center justify-center h-full">
                        <p class="text-sm text-gray-400">{{ t('chat.start_conversation') }}</p>
                    </div>

                    <template v-for="msg in messageList" :key="msg.id">
                        
                        <div v-if="msg.type === 'proposal' && msg.proposal" class="flex justify-center px-2">
                            <ProposalChatCard
                                :proposal="msg.proposal"
                                :current-user-id="auth_user_id"
                                :conversation-id="conversation.id"
                                @proposal-acted="handleProposalActed"
                            />
                        </div>

                        
                        <div
                            v-else-if="msg.type === 'text'"
                            class="flex"
                            :class="msg.sender_id === auth_user_id ? 'justify-end' : 'justify-start'"
                        >
                            <div v-if="msg.sender_id !== auth_user_id" class="w-7 h-7 rounded-full bg-navy flex items-center justify-center text-white font-bold text-xs flex-shrink-0 mr-2 mt-1">
                                {{ initials(msg.sender.name) }}
                            </div>

                            <div class="max-w-xs lg:max-w-md">
                                <div
                                    class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed"
                                    :class="msg.sender_id === auth_user_id
                                        ? 'bg-navy text-white rounded-tr-sm'
                                        : 'bg-white border border-gray-100 text-gray-800 rounded-tl-sm shadow-sm'"
                                >
                                    {{ msg.body }}
                                </div>
                                <p
                                    class="text-xs text-gray-400 mt-1 px-1"
                                    :class="msg.sender_id === auth_user_id ? 'text-right' : 'text-left'"
                                >
                                    {{ formatTime(msg.created_at) }}
                                </p>
                            </div>
                        </div>
                    </template>

                    <div ref="messagesEnd" />
                </div>

                
                <div class="bg-white border-t border-gray-100 px-4 py-3 flex items-end gap-3 flex-shrink-0">
                    <textarea
                        ref="textarea"
                        v-model="newMessage"
                        @keydown="handleKeydown"
                        rows="1"
                        :placeholder="t('chat.message_placeholder')"
                        class="flex-1 px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-none transition max-h-32"
                        style="field-sizing: content;"
                    />
                    <button
                        @click="sendMessage"
                        :disabled="!newMessage.trim() || sending"
                        class="p-2.5 bg-navy text-white rounded-xl hover:bg-navy/90 transition-colors disabled:opacity-40 flex-shrink-0"
                    >
                        <PaperAirplaneIcon class="w-5 h-5" />
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
