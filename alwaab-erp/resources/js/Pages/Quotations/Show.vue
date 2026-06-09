<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    quotation: Object,
    statuses: Array,
});

const page = usePage();
const { t, displayName, formatMoney, formatDate } = useLocale();
const permissions = computed(() => page.props.auth?.user?.permissions || []);

function statusLabel(value) {
    return props.statuses.find((s) => s.value === value)?.label || value;
}

function approve() {
    if (confirm(t('ui.confirm_approve'))) {
        router.post(`/quotations/${props.quotation.id}/approve`);
    }
}

function send() {
    if (confirm(t('ui.confirm_send'))) {
        router.post(`/quotations/${props.quotation.id}/send`);
    }
}

const statusColors = {
    draft: 'bg-slate-500/20 text-slate-300',
    sent: 'bg-cyan-500/20 text-cyan-300',
    approved: 'bg-green-500/20 text-green-300',
    rejected: 'bg-red-500/20 text-red-300',
    expired: 'bg-amber-500/20 text-amber-300',
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <Link href="/quotations" class="mb-2 inline-block text-xs text-slate-500 hover:text-cyan-400">← {{ t('ui.back_to_quotations') }}</Link>
                    <h1 class="text-2xl font-bold font-mono text-amber-400">{{ quotation.number }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ t('ui.version') }} {{ quotation.version }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="statusColors[quotation.status]">
                        {{ statusLabel(quotation.status) }}
                    </span>
                    <button
                        v-if="quotation.status === 'draft' && permissions.includes('quotations.approve')"
                        @click="approve"
                        class="rounded-lg bg-green-500 px-4 py-2 text-xs font-bold text-[#0a0f1e] hover:bg-green-400"
                    >
                        {{ t('ui.approve') }}
                    </button>
                    <button
                        v-if="quotation.status === 'approved' && permissions.includes('quotations.create')"
                        @click="send"
                        class="rounded-lg border border-cyan-500/30 px-4 py-2 text-xs text-cyan-400 hover:bg-cyan-500/10"
                    >
                        {{ t('ui.send_to_client') }}
                    </button>
                    <a
                        :href="`/quotations/${quotation.id}/pdf`"
                        target="_blank"
                        rel="noopener"
                        class="rounded-lg border border-cyan-500/30 px-4 py-2 text-xs text-cyan-400 hover:bg-cyan-500/10"
                    >
                        {{ t('ui.preview_pdf_en') }}
                    </a>
                    <a
                        :href="`/quotations/${quotation.id}/pdf?download=1`"
                        class="rounded-lg border border-amber-500/30 px-4 py-2 text-xs text-amber-400 hover:bg-amber-500/10"
                    >
                        {{ t('ui.download_pdf') }}
                    </a>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.customer') }}</p>
                    <p class="mt-1 font-medium">{{ displayName(quotation.contact) }}</p>
                    <p class="text-xs text-slate-500">{{ quotation.contact?.company }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.project') }}</p>
                    <p class="mt-1 font-medium">{{ quotation.project ? displayName(quotation.project) : '—' }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.valid_until') }}</p>
                    <p class="mt-1 font-medium">{{ quotation.valid_until ? formatDate(quotation.valid_until) : '—' }}</p>
                </div>
            </div>

            <div class="mb-6 overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-4 py-3 font-medium">#</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.product') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.quantity') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.unit') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.unit_price') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in quotation.items" :key="item.id" class="border-b border-cyan-500/5">
                            <td class="px-4 py-3 text-slate-500">{{ i + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-cyan-400">{{ item.product?.sku }}</span>
                                <p>{{ item.description || displayName(item.product) }}</p>
                            </td>
                            <td class="px-4 py-3">{{ item.quantity }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ item.unit }}</td>
                            <td class="px-4 py-3">{{ formatMoney(item.unit_price_aed) }}</td>
                            <td class="px-4 py-3 font-medium">{{ formatMoney(item.total_aed) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-cyan-500/10">
                            <td colspan="5" class="px-4 py-3 text-start text-slate-400">{{ t('ui.subtotal') }}</td>
                            <td class="px-4 py-3 font-medium">{{ formatMoney(quotation.subtotal_aed) }} AED</td>
                        </tr>
                        <tr v-if="quotation.discount_aed > 0">
                            <td colspan="5" class="px-4 py-2 text-start text-slate-400">{{ t('ui.discount') }}</td>
                            <td class="px-4 py-2 text-red-400">-{{ formatMoney(quotation.discount_aed) }} AED</td>
                        </tr>
                        <tr class="border-t border-cyan-500/20">
                            <td colspan="5" class="px-4 py-3 text-start font-bold text-amber-400">{{ t('ui.total') }}</td>
                            <td class="px-4 py-3 text-lg font-bold text-amber-400">{{ formatMoney(quotation.total_aed) }} AED</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div v-if="quotation.notes" class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                <p class="text-xs text-slate-500">{{ t('ui.notes') }}</p>
                <p class="mt-1 text-sm text-slate-300">{{ quotation.notes }}</p>
            </div>
        </div>
    </AppLayout>
</template>
