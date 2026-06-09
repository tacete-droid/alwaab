<script setup>
import AppToolbar from '@/Components/AppToolbar.vue';
import CompanyLogo from '@/Components/CompanyLogo.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import { useLocale } from '@/composables/useLocale';
import { startNotificationPoller, stopNotificationPoller } from '@/composables/useNotifications';
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash);
const drawerOpen = ref(false);
const { dir, t, displayName } = useLocale();

const nav = computed(() => [
    { href: '/', label: t('nav.dashboard'), icon: '📊', permission: 'dashboard.view' },
    { href: '/access', label: t('nav.access_control'), icon: '🔐', permission: 'users.view' },
    { href: '/contacts', label: t('nav.contacts'), icon: '👥', permission: 'contacts.view' },
    { href: '/invoices', label: t('nav.invoices'), icon: '🧾', permission: 'invoices.view' },
    { href: '/leads', label: t('nav.leads'), icon: '📋', permission: 'leads.view' },
    { href: '/projects', label: t('nav.projects'), icon: '🏗️', permission: 'projects.view' },
    { href: '/rfqs', label: t('nav.rfqs'), icon: '📋', permission: 'quotations.view' },
    { href: '/quotations', label: t('nav.quotations'), icon: '💰', permission: 'quotations.view' },
    { href: '/catalog/products', label: t('nav.catalog'), icon: '📦', permission: 'products.view' },
    { href: '/field-visits', label: t('nav.field_visits'), icon: '📍', permission: 'visits.view' },
    { href: '/chat', label: t('nav.chat'), icon: '💬', permission: 'chat.view' },
    { href: '/ai-studio', label: t('nav.ai_studio'), icon: '✨', permission: 'access-ai-studio' },
    { href: '/reports', label: t('nav.reports'), icon: '📊', permission: 'dashboard.view' },
    { href: '/audit-logs', label: t('nav.audit'), icon: '📋', permission: 'audit.view' },
    { href: '/hr/employees', label: t('nav.hr'), icon: '👨‍💼', permission: 'hr.view' },
    { href: '/hr/attendance', label: t('nav.attendance'), icon: '✅', permission: 'hr.view' },
    { href: '/directives', label: t('nav.directives'), icon: '📢', permission: 'settings.manage' },
    { href: '/settings', label: t('nav.settings'), icon: '⚙️', permission: 'settings.manage' },
]);

const bottomPriority = ['/', '/field-visits', '/hr/attendance', '/chat'];

const visibleNav = computed(() =>
    nav.value.filter((item) => !item.permission || user.value?.permissions?.includes(item.permission))
);

const bottomNavItems = computed(() => {
    const items = [];
    for (const href of bottomPriority) {
        const item = visibleNav.value.find((n) => n.href === href);
        if (item) items.push(item);
    }
    if (items.length < 4) {
        for (const item of visibleNav.value) {
            if (items.length >= 4) break;
            if (!items.find((b) => b.href === item.href)) items.push(item);
        }
    }
    return items.slice(0, 4);
});

const drawerNav = computed(() =>
    visibleNav.value.filter((n) => !bottomNavItems.value.find((b) => b.href === n.href))
);

const currentTitle = computed(() => {
    const url = page.url.split('?')[0];
    const match = visibleNav.value.find(
        (n) => url === n.href || (n.href !== '/' && url.startsWith(n.href))
    );
    return match?.label || 'ALWAAB';
});

function isActive(href) {
    const url = page.url.split('?')[0];
    return url === href || (href !== '/' && url.startsWith(href));
}

function logout() {
    drawerOpen.value = false;
    router.post('/logout');
}

function closeDrawer() {
    drawerOpen.value = false;
}

watch(() => page.url, closeDrawer);

onMounted(() => {
    document.body.style.overflow = '';
    startNotificationPoller();
});

watch(drawerOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

onUnmounted(() => {
    document.body.style.overflow = '';
    stopNotificationPoller();
});
</script>

<template>
    <div class="app-shell min-h-screen bg-app text-app" :dir="dir">
        <div v-if="flash?.success" class="bg-green-500/20 px-4 py-2 text-center text-sm text-green-400">
            {{ flash.success }}
        </div>
        <div v-if="flash?.error" class="bg-red-500/20 px-4 py-2 text-center text-sm text-red-400">
            {{ flash.error }}
        </div>
        <div v-if="flash?.warning" class="bg-amber-500/20 px-4 py-2 text-center text-sm text-amber-300">
            {{ flash.warning }}
        </div>

        <div class="flex min-h-screen">
            <!-- Desktop sidebar -->
            <aside class="app-sidebar relative hidden w-60 shrink-0 flex-col border-e border-app bg-app-surface md:flex">
                <div class="border-b border-app px-5 py-5">
                    <Link href="/"><CompanyLogo size="md" /></Link>
                </div>
                <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                    <Link
                        v-for="item in visibleNav"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                        :class="isActive(item.href)
                            ? 'bg-cyan-500/10 text-cyan-400'
                            : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'"
                    >
                        <span>{{ item.icon }}</span>
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>
                <div v-if="user" class="border-t border-app p-4">
                    <Link href="/profile" class="block truncate text-sm font-medium hover:text-cyan-400">
                        {{ displayName(user) }}
                    </Link>
                    <p class="truncate text-xs text-slate-500">{{ user.email }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        <Link href="/profile" class="text-xs text-cyan-400 hover:text-cyan-300">{{ t('layout.profile') }}</Link>
                        <button @click="logout" class="text-xs text-red-400 hover:text-red-300">{{ t('layout.logout') }}</button>
                    </div>
                </div>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col">
                <!-- Desktop top bar -->
                <header class="app-topbar hidden shrink-0 items-center justify-between gap-4 border-b border-app bg-app-surface/90 px-6 py-3 backdrop-blur md:flex">
                    <h2 class="truncate text-sm font-semibold text-cyan-400">{{ currentTitle }}</h2>
                    <AppToolbar />
                </header>

                <!-- Mobile header -->
                <header class="app-header sticky top-0 z-30 border-b border-app bg-app-surface/95 px-4 py-3 backdrop-blur md:hidden">
                    <div class="flex items-center justify-between gap-3">
                        <button
                            @click="drawerOpen = true"
                            class="toolbar-btn shrink-0"
                            :aria-label="t('layout.menu')"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="min-w-0 flex-1 text-center">
                            <p class="truncate text-sm font-bold text-cyan-400">{{ currentTitle }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <NotificationBell compact />
                            <Link href="/profile" class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-500/10 text-sm font-bold text-cyan-400">
                                {{ displayName(user)?.charAt(0) || '?' }}
                            </Link>
                        </div>
                    </div>
                </header>

                <main class="theme-scope flex-1 bg-app p-4 pb-24 md:p-6 md:pb-6">
                    <slot />
                </main>

                <!-- Mobile bottom navigation -->
                <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-app bg-app-surface/95 backdrop-blur md:hidden safe-bottom">
                    <div class="flex items-stretch justify-around">
                        <Link
                            v-for="item in bottomNavItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex min-h-[56px] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-2 transition"
                            :class="isActive(item.href) ? 'text-cyan-400' : 'text-slate-500'"
                        >
                            <span class="text-lg leading-none">{{ item.icon }}</span>
                            <span class="max-w-full truncate text-[10px] font-medium">{{ item.label.split(' ')[0] }}</span>
                        </Link>
                        <button
                            @click="drawerOpen = true"
                            class="flex min-h-[56px] flex-1 flex-col items-center justify-center gap-0.5 px-1 py-2 text-slate-500"
                        >
                            <span class="text-lg leading-none">☰</span>
                            <span class="text-[10px] font-medium">{{ t('layout.more') }}</span>
                        </button>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Mobile drawer -->
        <Teleport to="body">
            <div v-if="drawerOpen" class="fixed inset-0 z-50 md:hidden">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeDrawer" />
                <aside class="app-drawer absolute inset-y-0 end-0 flex w-[min(85vw,320px)] flex-col bg-app-surface shadow-2xl">
                    <div class="flex items-center justify-between border-b border-app px-5 py-4">
                        <CompanyLogo size="md" />
                        <button @click="closeDrawer" class="rounded-lg px-3 py-1 text-slate-400 hover:bg-white/5">{{ t('layout.close') }}</button>
                    </div>

                    <div v-if="user" class="border-b border-app px-5 py-4">
                        <Link href="/profile" @click="closeDrawer" class="block font-medium hover:text-cyan-400">
                            {{ displayName(user) }}
                        </Link>
                        <p class="text-xs text-slate-500">{{ user.email }}</p>
                    </div>

                    <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                        <Link
                            v-for="item in drawerNav"
                            :key="item.href"
                            :href="item.href"
                            @click="closeDrawer"
                            class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm transition"
                            :class="isActive(item.href)
                                ? 'bg-cyan-500/10 text-cyan-400'
                                : 'text-slate-400 hover:bg-white/5'"
                        >
                            <span>{{ item.icon }}</span>
                            <span>{{ item.label }}</span>
                        </Link>
                        <Link
                            href="/profile"
                            @click="closeDrawer"
                            class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm text-slate-400 hover:bg-white/5"
                        >
                            <span>👤</span>
                            <span>{{ t('layout.profile') }}</span>
                        </Link>
                    </nav>

                    <div class="border-t border-app p-4">
                        <div class="mb-4 flex justify-center">
                            <AppToolbar />
                        </div>
                        <button @click="logout" class="w-full rounded-lg bg-red-500/10 py-3 text-sm text-red-400">
                            {{ t('layout.logout') }}
                        </button>
                    </div>
                </aside>
            </div>
        </Teleport>
    </div>
</template>
