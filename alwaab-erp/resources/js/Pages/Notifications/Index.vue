<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router } from '@inertiajs/vue3';
import { requestNotificationPermission } from '@/composables/useNotifications';

defineProps({ notifications: Object, unread_count: Number });

const { t, intlLocale } = useLocale();

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString(intlLocale.value);
}

const categoryColors = {
    chat: 'border-cyan-500/20',
    directive: 'border-amber-500/20',
    system: 'border-green-500/20',
};

function markRead(id, url) {
    router.post(`/notifications/${id}/read`, {}, {
        onSuccess: () => { if (url) router.visit(url); },
    });
}

function markAllRead() {
    router.post('/notifications/read-all');
}

async function enableAlerts() {
    await requestNotificationPermission();
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-xl font-bold md:text-2xl">{{ t('notifications.title') }}</h1>
                <div class="flex items-center gap-2">
                    <button @click="enableAlerts" class="rounded-lg border border-amber-500/30 px-3 py-2 text-xs text-amber-400">
                        🔔 {{ t('notifications.enable_alerts') }}
                    </button>
                    <button
                        v-if="unread_count > 0"
                        @click="markAllRead"
                        class="rounded-lg bg-cyan-500/10 px-3 py-2 text-xs text-cyan-400"
                    >
                        {{ t('ui.mark_all_read') }}
                    </button>
                </div>
            </div>

            <div class="space-y-3">
                <button
                    v-for="n in notifications.data"
                    :key="n.id"
                    @click="markRead(n.id, n.url)"
                    class="w-full rounded-xl border bg-[#1a2540] p-4 text-start transition active:scale-[0.99] hover:bg-[#1f2d4d]"
                    :class="[categoryColors[n.category] || 'border-white/10', !n.read_at ? 'ring-1 ring-cyan-500/30' : 'opacity-75']"
                >
                    <div class="mb-1 flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span>{{ n.icon }}</span>
                            <span class="font-medium" :class="!n.read_at ? 'text-cyan-300' : 'text-slate-300'">{{ n.title }}</span>
                        </div>
                        <span v-if="n.priority === 'urgent'" class="shrink-0 rounded bg-red-500/20 px-2 py-0.5 text-[10px] text-red-400">{{ t('notifications.priority_urgent') }}</span>
                    </div>
                    <p class="text-sm text-slate-400">{{ n.body }}</p>
                    <p class="mt-2 text-[10px] text-slate-600">{{ formatDateTime(n.created_at) }}</p>
                </button>
            </div>

            <p v-if="!notifications.data?.length" class="py-16 text-center text-slate-500">{{ t('notifications.empty') }}</p>
        </div>
    </AppLayout>
</template>
