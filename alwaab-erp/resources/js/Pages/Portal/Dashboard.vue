<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { useLocale } from '@/composables/useLocale';

defineProps({
    contact: Object,
    stats: Object,
});

const { t, displayName } = useLocale();
</script>

<template>
    <PortalLayout>
        <h1 class="mb-2 text-2xl font-bold text-amber-400">{{ t('portal.welcome') }}</h1>
        <p v-if="contact" class="mb-8 text-slate-400">{{ contact.company }} — {{ displayName(contact) }}</p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-amber-500/15 bg-[#1a2540] p-5 text-center">
                <p class="text-3xl font-bold text-amber-400">{{ stats.rfqs }}</p>
                <p class="text-sm text-slate-500">{{ t('portal.my_rfqs') }}</p>
            </div>
            <div class="rounded-xl border border-amber-500/15 bg-[#1a2540] p-5 text-center">
                <p class="text-3xl font-bold text-cyan-400">{{ stats.quotations }}</p>
                <p class="text-sm text-slate-500">{{ t('portal.my_quotations') }}</p>
            </div>
            <div class="rounded-xl border border-amber-500/15 bg-[#1a2540] p-5 text-center">
                <p class="text-3xl font-bold text-green-400">{{ stats.pending_rfqs }}</p>
                <p class="text-sm text-slate-500">{{ t('reports.pending_quotes') }}</p>
            </div>
        </div>
    </PortalLayout>
</template>
