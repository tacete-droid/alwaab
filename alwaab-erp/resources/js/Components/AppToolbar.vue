<script setup>
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps({
    showNotifications: { type: Boolean, default: true },
    showTheme: { type: Boolean, default: true },
    showLanguage: { type: Boolean, default: true },
});
</script>

<template>
    <div class="app-toolbar" dir="ltr">
        <NotificationBell v-if="showNotifications" compact />
        <span v-if="showNotifications && (showTheme || showLanguage)" class="app-toolbar__divider" aria-hidden="true" />
        <ThemeToggle v-if="showTheme" />
        <span v-if="showTheme && showLanguage" class="app-toolbar__divider" aria-hidden="true" />
        <LanguageSwitcher v-if="showLanguage" />
    </div>
</template>
