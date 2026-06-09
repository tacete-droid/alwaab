<script setup>
import { router } from '@inertiajs/vue3';
import { useLocale } from '@/composables/useLocale';

const { locale, t } = useLocale();

function switchLocale(newLocale) {
    if (newLocale === locale.value) return;
    router.post('/locale', { locale: newLocale }, { preserveScroll: true });
}
</script>

<template>
    <div class="lang-switcher" dir="ltr" role="group" :aria-label="t('profile.language')">
        <button
            type="button"
            class="lang-switcher__btn"
            :class="{ 'lang-switcher__btn--active': locale === 'ar' }"
            @click="switchLocale('ar')"
        >
            {{ t('layout.locale_ar') }}
        </button>
        <button
            type="button"
            class="lang-switcher__btn"
            :class="{ 'lang-switcher__btn--active': locale === 'en' }"
            @click="switchLocale('en')"
        >
            {{ t('layout.locale_en') }}
        </button>
    </div>
</template>
