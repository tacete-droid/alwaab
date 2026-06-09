<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { useLocale } from '@/composables/useLocale';

defineProps({ products: Object });

const { t, displayName, formatMoney } = useLocale();
</script>

<template>
    <PortalLayout>
        <h1 class="mb-6 text-xl font-bold">{{ t('ui.flowguard_catalog') }}</h1>
        <div class="overflow-x-auto rounded-xl border border-amber-500/15 bg-[#1a2540]">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-amber-500/10 text-start text-slate-400">
                        <th class="px-4 py-3">{{ t('ui.sku') }}</th>
                        <th class="px-4 py-3">{{ t('ui.product') }}</th>
                        <th class="px-4 py-3">{{ t('ui.size') }}</th>
                        <th class="px-4 py-3">{{ t('ui.unit') }}</th>
                        <th class="px-4 py-3">{{ t('ui.price_aed') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in products.data" :key="p.id" class="border-b border-white/5">
                        <td class="px-4 py-3 font-mono text-amber-400">{{ p.sku }}</td>
                        <td class="px-4 py-3">{{ displayName(p) }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ p.size || '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ p.unit }}</td>
                        <td class="px-4 py-3 font-medium">{{ formatMoney(p.price_aed) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </PortalLayout>
</template>
