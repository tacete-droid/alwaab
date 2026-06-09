<script setup>
import { useLocale } from '@/composables/useLocale';
import { Link } from '@inertiajs/vue3';
import { unreadCount, permissionGranted, requestNotificationPermission } from '@/composables/useNotifications';

defineProps({
    compact: { type: Boolean, default: false },
});

const { t } = useLocale();

async function enableAlerts() {
    await requestNotificationPermission();
}
</script>

<template>
    <div class="flex items-center" :class="compact ? '' : 'gap-2'">
        <button
            v-if="!permissionGranted && !compact"
            @click="enableAlerts"
            class="rounded-lg bg-amber-500/10 px-2 py-1 text-[10px] text-amber-400 hover:bg-amber-500/20"
            :title="t('notifications.enable_alerts')"
        >
            {{ t('notifications.enable_alerts') }}
        </button>

        <Link
            href="/notifications"
            class="toolbar-btn relative"
            :title="t('notifications.title')"
        >
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -end-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-0.5 text-[9px] font-bold leading-none text-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </Link>
    </div>
</template>
