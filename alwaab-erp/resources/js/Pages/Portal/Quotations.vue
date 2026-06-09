<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { useLocale } from '@/composables/useLocale';

defineProps({ quotations: Object });

const { t, enumLabel, displayName, formatMoney } = useLocale();
</script>

<template>
    <PortalLayout>
        <h1 class="mb-6 text-xl font-bold">{{ t('portal.my_quotations') }}</h1>
        <div v-if="quotations.data?.length" class="space-y-3">
            <div v-for="q in quotations.data" :key="q.id" class="rounded-xl border border-amber-500/15 bg-[#1a2540] p-4">
                <div class="flex flex-wrap justify-between gap-2">
                    <span class="font-mono text-amber-400">{{ q.number }}</span>
                    <span class="text-lg font-bold text-cyan-400">{{ formatMoney(q.total_aed) }} {{ t('ui.currency_aed') }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ q.project ? displayName(q.project) : '—' }} — {{ enumLabel('quotations.statuses', q.status) }}</p>
            </div>
        </div>
        <p v-else class="text-center text-slate-500">{{ t('ui.no_quotations') }}</p>
    </PortalLayout>
</template>
