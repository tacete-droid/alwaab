<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    kpis: Object,
});

const { t, formatNumber } = useLocale();

const quickLinks = computed(() => [
    { href: '/catalog/products', label: t('nav.catalog'), icon: '📦' },
    { href: '/quotations', label: t('nav.quotations'), icon: '💰' },
    { href: '/field-visits/map', label: t('nav.visit_map'), icon: '📍' },
    { href: '/reports', label: t('nav.reports'), icon: '📊' },
    { href: '/chat', label: t('nav.chat'), icon: '💬' },
    { href: '/hr/attendance', label: t('hr.attendance'), icon: '👨‍💼' },
]);
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <h2 class="mb-6 text-2xl font-bold">{{ t('dashboard.kpis_title') }}</h2>

            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <p class="text-xs text-slate-400">{{ t('dashboard.ytd_sales') }}</p>
                    <p class="mt-1 text-2xl font-bold text-cyan-400">
                        {{ formatNumber(kpis?.sales?.ytd_aed || 0) }}
                        <span class="text-sm font-normal text-slate-400">AED</span>
                    </p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <p class="text-xs text-slate-400">{{ t('dashboard.active_projects') }}</p>
                    <p class="mt-1 text-2xl font-bold text-teal-400">{{ kpis?.projects?.active || 0 }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <p class="text-xs text-slate-400">{{ t('dashboard.contacts') }}</p>
                    <p class="mt-1 text-2xl font-bold text-amber-400">{{ kpis?.crm?.contacts || 0 }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <p class="text-xs text-slate-400">{{ t('dashboard.stock_alerts') }}</p>
                    <p class="mt-1 text-2xl font-bold text-red-400">{{ kpis?.inventory?.low_stock_items || 0 }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <h3 class="mb-4 font-semibold text-cyan-400">{{ t('dashboard.leads_pipeline') }}</h3>
                    <div class="space-y-2">
                        <div
                            v-for="(count, status) in kpis?.crm?.leads_pipeline"
                            :key="status"
                            class="flex justify-between rounded-lg bg-[#0f172a] px-4 py-2 text-sm"
                        >
                            <span class="text-slate-300">{{ status }}</span>
                            <span class="font-bold text-cyan-400">{{ count }}</span>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <h3 class="mb-4 font-semibold text-cyan-400">{{ t('dashboard.field_visits') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-[#0f172a] p-4 text-center">
                            <p class="text-3xl font-bold text-green-400">{{ kpis?.field_visits?.today || 0 }}</p>
                            <p class="text-xs text-slate-400">{{ t('dashboard.today') }}</p>
                        </div>
                        <div class="rounded-lg bg-[#0f172a] p-4 text-center">
                            <p class="text-3xl font-bold text-purple-400">{{ kpis?.field_visits?.this_month || 0 }}</p>
                            <p class="text-xs text-slate-400">{{ t('dashboard.this_month') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="mb-4 font-semibold text-cyan-400">{{ t('dashboard.quick_access') }}</h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    <Link
                        v-for="link in quickLinks"
                        :key="link.href"
                        :href="link.href"
                        class="flex min-h-[72px] flex-col items-center justify-center gap-2 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4 text-center transition active:scale-95 hover:border-cyan-500/30 sm:flex-row sm:text-start"
                    >
                        <span class="text-2xl">{{ link.icon }}</span>
                        <span class="text-xs sm:text-sm">{{ link.label }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
