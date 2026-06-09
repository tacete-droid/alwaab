<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { getEcho } from '@/echo';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    conversations: Array,
    activeConversationId: String,
    messages: Array,
    users: Array,
});

const page = usePage();
const { t, displayName, intlLocale } = useLocale();
const showNewChat = ref(false);
const activeId = ref(props.activeConversationId);
const messageList = ref(props.messages || []);
const conversationList = ref(props.conversations || []);
const isMobile = ref(false);
const connectionStatus = ref('connecting');

const newChatForm = useForm({ user_id: '' });
const messageForm = useForm({ body: '' });

let mediaQuery = null;
const subscribedIds = new Set();

function formatTime(date) {
    if (!date) return '';
    return new Date(date).toLocaleTimeString(intlLocale.value);
}

function selectConversation(id) {
    activeId.value = id;
    router.get('/chat', { conversation_id: id }, { preserveState: true });
}

function backToList() {
    activeId.value = null;
    router.get('/chat', {}, { preserveState: true });
}

function startChat() {
    newChatForm.post('/chat', {
        onSuccess: () => { showNewChat.value = false; newChatForm.reset(); },
    });
}

function sendMessage() {
    if (!activeId.value || !messageForm.body.trim()) return;
    messageForm.post(`/chat/${activeId.value}/messages`, {
        preserveScroll: true,
        onSuccess: () => messageForm.reset(),
    });
}

function handleIncomingMessage(payload) {
    if (messageList.value.some((m) => m.id === payload.id)) return;

    if (activeId.value === payload.conversation_id) {
        messageList.value.push({
            id: payload.id,
            sender_id: payload.sender_id,
            body: payload.body,
            created_at: payload.created_at,
            sender: payload.sender,
        });
    }

    const idx = conversationList.value.findIndex((c) => c.id === payload.conversation_id);
    if (idx >= 0) {
        const conv = { ...conversationList.value[idx] };
        conv.last_message = {
            body: payload.body,
            sender: displayName(payload.sender),
            created_at: payload.created_at,
        };
        conversationList.value.splice(idx, 1);
        conversationList.value.unshift(conv);
    }
}

function ensureSubscriptions() {
    const echo = getEcho();
    if (!echo) {
        connectionStatus.value = 'offline';
        return;
    }

    connectionStatus.value = 'connected';

    conversationList.value.forEach((conv) => {
        if (subscribedIds.has(conv.id)) return;
        subscribedIds.add(conv.id);
        echo.private(`conversation.${conv.id}`)
            .listen('.message.sent', (payload) => handleIncomingMessage(payload));
    });
}

const showList = computed(() => !isMobile.value || !activeId.value);
const showMessages = computed(() => !isMobile.value || activeId.value);

watch(() => props.messages, (val) => { messageList.value = val || []; });
watch(() => props.conversations, (val) => { conversationList.value = val || []; });
watch(() => props.activeConversationId, (val) => { activeId.value = val; });

watch(conversationList, () => ensureSubscriptions(), { deep: true });

onMounted(() => {
    mediaQuery = window.matchMedia('(max-width: 767px)');
    isMobile.value = mediaQuery.matches;
    mediaQuery.addEventListener('change', (e) => { isMobile.value = e.matches; });
    ensureSubscriptions();
});

onUnmounted(() => {
    mediaQuery?.removeEventListener('change', () => {});
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-6xl">
            <div v-if="showList" class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold md:text-2xl">{{ t('chat.title') }}</h1>
                    <span
                        class="rounded-full px-2 py-0.5 text-[10px]"
                        :class="connectionStatus === 'connected' ? 'bg-green-500/20 text-green-400' : 'bg-slate-500/20 text-slate-500'"
                    >
                        {{ connectionStatus === 'connected' ? '● ' + t('ui.live') : t('ui.no_data') }}
                    </span>
                </div>
                <button @click="showNewChat = !showNewChat" class="touch-target rounded-lg bg-cyan-500 px-4 py-2.5 text-sm font-bold text-[#0a0f1e]">
                    + {{ t('ui.new') }}
                </button>
            </div>

            <div v-if="showNewChat && showList" class="mb-4 flex flex-col gap-2 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4 sm:flex-row">
                <select v-model="newChatForm.user_id" class="flex-1 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-3 text-sm outline-none">
                    <option value="">{{ t('chat.select_user') }}</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ displayName(u) }}</option>
                </select>
                <button @click="startChat" class="touch-target rounded-lg bg-cyan-500 px-6 py-3 text-sm font-bold text-[#0a0f1e]">{{ t('chat.start_chat') }}</button>
            </div>

            <div class="flex overflow-hidden rounded-xl border border-cyan-500/15 bg-[#1a2540]" :class="isMobile ? 'h-[calc(100dvh-180px)]' : 'h-[calc(100vh-220px)] min-h-[400px]'">
                <div v-show="showList" class="w-full shrink-0 overflow-y-auto border-s border-cyan-500/10 md:w-72">
                    <button
                        v-for="conv in conversationList"
                        :key="conv.id"
                        @click="selectConversation(conv.id)"
                        class="w-full border-b border-white/5 px-4 py-4 text-start transition hover:bg-white/5 active:bg-cyan-500/10"
                        :class="activeId === conv.id ? 'bg-cyan-500/10' : ''"
                    >
                        <p class="truncate text-sm font-medium">{{ conv.name }}</p>
                        <p v-if="conv.last_message" class="truncate text-xs text-slate-500">{{ conv.last_message.body }}</p>
                    </button>
                    <p v-if="!conversationList?.length" class="p-6 text-center text-sm text-slate-500">{{ t('chat.no_conversations') }}</p>
                </div>

                <div v-show="showMessages" class="flex flex-1 flex-col">
                    <div v-if="isMobile && activeId" class="flex items-center gap-3 border-b border-cyan-500/10 px-4 py-3">
                        <button @click="backToList" class="touch-target text-cyan-400">→ {{ t('ui.back') }}</button>
                        <span class="truncate text-sm font-medium">
                            {{ conversationList?.find(c => c.id === activeId)?.name }}
                        </span>
                    </div>

                    <template v-if="activeId">
                        <div class="flex-1 space-y-3 overflow-y-auto p-4">
                            <div
                                v-for="msg in messageList"
                                :key="msg.id"
                                class="flex"
                                :class="msg.sender_id === page.props.auth.user.id ? 'justify-start' : 'justify-end'"
                            >
                                <div
                                    class="max-w-[85%] rounded-xl px-4 py-2 text-sm md:max-w-[75%]"
                                    :class="msg.sender_id === page.props.auth.user.id
                                        ? 'bg-cyan-500/20 text-cyan-100'
                                        : 'bg-[#0f172a] text-slate-200'"
                                >
                                    <p class="mb-1 text-xs text-slate-500">{{ displayName(msg.sender) }}</p>
                                    <p>{{ msg.body }}</p>
                                    <p class="mt-1 text-[10px] text-slate-600">{{ formatTime(msg.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                        <form @submit.prevent="sendMessage" class="flex gap-2 border-t border-cyan-500/10 p-3 md:p-4">
                            <input
                                v-model="messageForm.body"
                                :placeholder="t('chat.type_message')"
                                class="flex-1 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none focus:border-cyan-400"
                            />
                            <button type="submit" :disabled="messageForm.processing" class="touch-target shrink-0 rounded-lg bg-cyan-500 px-5 py-3 text-sm font-bold text-[#0a0f1e]">
                                {{ t('chat.send') }}
                            </button>
                        </form>
                    </template>
                    <div v-else-if="!isMobile" class="flex flex-1 items-center justify-center text-slate-500">
                        {{ t('chat.start_chat') }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
