<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tab: String,
    sales: Object,
    projects: Object,
    inventory: Object,
    crm: Object,
});

const { t, enumLabel, formatMoney, locale } = useLocale();
const activeTab = ref(props.tab || 'sales');

const tabs = computed(() => [
    { id: 'sales', label: t('reports.sales') },
    { id: 'projects', label: t('reports.projects') },
    { id: 'inventory', label: t('reports.inventory') },
    { id: 'crm', label: t('reports.crm') },
]);

const monthFormatter = computed(() =>
    new Intl.DateTimeFormat(locale.value === 'ar' ? 'ar-AE' : 'en-AE', { month: 'short' })
);

function switchTab(tab) {
    activeTab.value = tab;
    router.get('/reports', { tab }, { preserveState: true });
}

function monthLabel(month) {
    return monthFormatter.value.format(new Date(2024, month - 1, 1));
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <h1 class="mb-6 text-2xl font-bold">{{ t('reports.title') }}</h1>

            <div class="mb-6 flex flex-wrap gap-2">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="switchTab(tab.id)"
                    class="rounded-lg px-4 py-2 text-sm transition"
                    :class="activeTab === tab.id ? 'bg-cyan-500 font-bold text-[#0a0f1e]' : 'bg-[#1a2540] text-slate-400 hover:text-slate-200'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <div v-if="activeTab === 'sales'" class="space-y-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                        <p class="text-xs text-slate-500">{{ t('reports.ytd_sales') }}</p>
                        <p class="mt-1 text-2xl font-bold text-cyan-400">{{ formatMoney(sales.ytd_total) }} AED</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                        <p class="text-xs text-slate-500">{{ t('reports.approved_quotes') }}</p>
                        <p class="mt-1 text-2xl font-bold text-green-400">{{ sales.approved_count }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                        <p class="text-xs text-slate-500">{{ t('reports.pending_quotes') }}</p>
                        <p class="mt-1 text-2xl font-bold text-amber-400">{{ sales.pending_count }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <h3 class="mb-4 font-semibold text-cyan-400">{{ t('reports.sales') }}</h3>
                    <div class="flex items-end gap-2 h-40">
                        <div v-for="m in sales.monthly" :key="m.month" class="flex flex-1 flex-col items-center gap-1">
                            <div
                                class="w-full rounded-t bg-cyan-500/60"
                                :style="{ height: Math.max(4, (m.total / (sales.ytd_total || 1)) * 120) + 'px' }"
                            />
                            <span class="text-[10px] text-slate-500">{{ monthLabel(m.month) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'projects'" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div v-for="row in projects.by_status" :key="row.status" class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                        <p class="text-xs text-slate-500">{{ enumLabel('crm.project_statuses', row.status) }}</p>
                        <p class="text-xl font-bold">{{ row.count }}</p>
                        <p class="text-sm text-cyan-400">{{ formatMoney(row.value) }} AED</p>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'inventory'" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                        <p class="text-xs text-slate-500">{{ t('ui.product') }}</p>
                        <p class="text-2xl font-bold">{{ inventory.total_skus }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                        <p class="text-xs text-slate-500">{{ t('reports.low_stock') }}</p>
                        <p class="text-2xl font-bold text-red-400">{{ inventory.low_stock_count }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                        <p class="text-xs text-slate-500">{{ t('reports.stock_value') }}</p>
                        <p class="text-2xl font-bold text-amber-400">{{ formatMoney(inventory.total_value) }} AED</p>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'crm'" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <p class="text-xs text-slate-500">{{ t('crm.leads') }}</p>
                    <p class="text-2xl font-bold text-green-400">{{ formatMoney(crm.won_value) }} AED</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <p class="text-xs text-slate-500">{{ t('dashboard.active_projects') }}</p>
                    <p class="text-2xl font-bold text-teal-400">{{ crm.active_projects }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
