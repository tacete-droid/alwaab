<script setup>
import AppToolbar from '@/Components/AppToolbar.vue';
import CompanyLogo from '@/Components/CompanyLogo.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash);
const { dir, t, displayName } = useLocale();

const nav = computed(() => [
    { href: '/portal', label: t('nav.portal_home'), icon: '🏠', short: t('nav.portal_home') },
    { href: '/portal/catalog', label: t('nav.portal_catalog'), icon: '📦', short: t('nav.portal_catalog') },
    { href: '/portal/rfqs', label: t('nav.portal_rfqs'), icon: '📋', short: t('nav.portal_rfqs') },
    { href: '/portal/quotations', label: t('nav.portal_quotations'), icon: '💰', short: t('nav.portal_quotations') },
]);

function isActive(href) {
    const url = page.url.split('?')[0];
    return url === href || (href !== '/portal' && url.startsWith(href));
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="app-shell min-h-screen bg-app text-app" :dir="dir">
        <div v-if="flash?.success" class="bg-green-500/20 px-4 py-2 text-center text-sm text-green-400">{{ flash.success }}</div>
        <div v-if="flash?.error" class="bg-red-500/20 px-4 py-2 text-center text-sm text-red-400">{{ flash.error }}</div>

        <header class="app-header border-b border-app bg-app-surface">
            <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6 md:py-4">
                <div class="flex min-w-0 items-center gap-2">
                    <CompanyLogo size="sm" />
                    <span class="hidden text-xs text-slate-500 sm:inline">{{ t('layout.portal_subtitle') }}</span>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <AppToolbar :show-notifications="false" />
                    <div v-if="user" class="flex items-center gap-3 border-s border-app ps-3">
                        <span class="hidden text-sm text-slate-400 sm:inline">{{ displayName(user) }}</span>
                        <button @click="logout" class="min-h-[40px] rounded-lg px-3 text-xs text-red-400 hover:bg-red-500/10">{{ t('layout.portal_logout') }}</button>
                    </div>
                </div>
            </div>
            <nav class="hidden gap-1 overflow-x-auto px-6 pb-3 md:flex md:mx-auto md:max-w-7xl">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm whitespace-nowrap transition"
                    :class="isActive(item.href)
                        ? 'bg-amber-500/10 text-amber-400'
                        : 'text-slate-400 hover:bg-white/5'"
                >
                    <span>{{ item.icon }}</span>
                    <span>{{ item.label }}</span>
                </Link>
            </nav>
        </header>

        <main class="theme-scope mx-auto max-w-7xl bg-app p-4 pb-24 md:p-6 md:pb-6">
            <slot />
        </main>

        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-app bg-app-surface/95 backdrop-blur md:hidden safe-bottom">
            <div class="flex items-stretch justify-around">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="flex min-h-[56px] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-2 transition"
                    :class="isActive(item.href) ? 'text-amber-400' : 'text-slate-500'"
                >
                    <span class="text-lg leading-none">{{ item.icon }}</span>
                    <span class="text-[10px] font-medium">{{ item.short }}</span>
                </Link>
            </div>
        </nav>
    </div>
</template>
