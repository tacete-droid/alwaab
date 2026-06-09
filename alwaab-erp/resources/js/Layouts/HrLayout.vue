<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const { t } = useLocale();

const tabs = computed(() => [
    { href: '/hr/employees', label: t('nav.employees'), icon: '👥' },
    { href: '/hr/attendance', label: t('hr.attendance'), icon: '✅' },
    { href: '/hr/leaves', label: t('nav.leaves'), icon: '🏖️' },
]);
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <h1 class="mb-3 text-xl font-bold text-green-400 md:mb-4 md:text-2xl">{{ t('nav.hr') }}</h1>
            <nav class="mb-4 -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 md:mb-6 md:flex-wrap md:overflow-visible">
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="flex shrink-0 items-center gap-2 rounded-lg px-4 py-2.5 text-sm transition min-h-[44px]"
                    :class="page.url.startsWith(tab.href)
                        ? 'bg-green-500/20 font-medium text-green-400'
                        : 'bg-[#1a2540] text-slate-400 hover:bg-white/5'"
                >
                    <span>{{ tab.icon }}</span>
                    <span>{{ tab.label }}</span>
                </Link>
            </nav>
            <slot />
        </div>
    </AppLayout>
</template>
