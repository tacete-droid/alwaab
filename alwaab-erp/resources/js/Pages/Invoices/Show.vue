<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    invoice: Object,
    types: Array,
    statuses: Array,
});

const page = usePage();
const { t, locale, displayName, formatMoney } = useLocale();
const permissions = computed(() => page.props.auth?.user?.permissions || []);

function sendToClient() {
    if (confirm(t('ui.confirm_send'))) {
        router.post(`/invoices/${props.invoice.id}/send`);
    }
}

function typeLabel(value) {
    const typeItem = props.types.find((x) => x.value === value);
    return locale.value === 'ar' ? typeItem?.label_ar : typeItem?.label_en;
}

function statusLabel(value) {
    return props.statuses.find((s) => s.value === value)?.label || value;
}

const afterDiscount = computed(() =>
    Math.max(0, Number(props.invoice.subtotal_aed) - Number(props.invoice.discount_aed))
);
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <Link href="/invoices" class="text-xs text-cyan-400 hover:underline">← {{ t('ui.back_to_invoices') }}</Link>
                    <h1 class="mt-2 text-2xl font-bold font-mono text-cyan-400">{{ invoice.number }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ typeLabel(invoice.type) }} — {{ statusLabel(invoice.status) }}
                        <span v-if="invoice.source === 'website'" class="ms-2 rounded-full bg-orange-500/20 px-2 py-0.5 text-xs text-orange-300">🌐 {{ t('invoices.source_website') }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-if="invoice.status === 'saved' && permissions.includes('invoices.create')"
                        @click="sendToClient"
                        class="rounded-lg border border-cyan-500/30 px-4 py-2 text-sm text-cyan-400 hover:bg-cyan-500/10"
                    >
                        {{ t('ui.send_to_client') }}
                    </button>
                    <a :href="`/invoices/${invoice.id}/pdf`" target="_blank" rel="noopener" class="rounded-lg border border-cyan-500/30 px-4 py-2 text-sm text-cyan-400 hover:bg-cyan-500/10">
                        {{ t('ui.preview_pdf_en') }}
                    </a>
                    <a :href="`/invoices/${invoice.id}/pdf?download=1`" class="rounded-lg border border-amber-500/30 px-4 py-2 text-sm text-amber-400 hover:bg-amber-500/10">
                        {{ t('ui.download_pdf') }}
                    </a>
                    <a :href="`/invoices/${invoice.id}/excel`" class="rounded-lg border border-green-500/30 px-4 py-2 text-sm text-green-400 hover:bg-green-500/10">
                        {{ t('ui.export_excel') }}
                    </a>
                </div>
            </div>

            <div class="mb-4 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                <h2 class="mb-3 text-sm font-semibold text-slate-300">👤 {{ t('ui.customer') }} / {{ t('ui.project') }}</h2>
                <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2 lg:grid-cols-3">
                    <div><span class="text-slate-500">{{ t('ui.date') }}:</span> {{ invoice.document_date }}</div>
                    <div><span class="text-slate-500">{{ t('ui.customer') }}:</span> {{ invoice.client_name || (invoice.contact ? displayName(invoice.contact) : '—') }}</div>
                    <div><span class="text-slate-500">{{ t('ui.attention_to') }}:</span> {{ invoice.attention_to || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.project') }}:</span> {{ invoice.project_name || (invoice.project ? displayName(invoice.project) : '—') }}</div>
                    <div><span class="text-slate-500">{{ t('ui.consultant') }}:</span> {{ invoice.consultant || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.contractor') }}:</span> {{ invoice.main_contractor || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.mep_contractor') }}:</span> {{ invoice.mep_contractor || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.phone') }}:</span> {{ invoice.phone || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.email') }}:</span> {{ invoice.email || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.lpo_number') }}:</span> {{ invoice.lpo_number || '—' }}</div>
                    <div class="md:col-span-2"><span class="text-slate-500">{{ t('ui.address') }}:</span> {{ invoice.address || '—' }}</div>
                    <div v-if="invoice.valid_until"><span class="text-slate-500">{{ t('ui.valid_until') }}:</span> {{ invoice.valid_until }}</div>
                </div>
            </div>

            <div class="mb-4 overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-4 py-3 font-medium">#</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.description') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.quantity') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.unit') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.price_aed') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.total') }} (AED)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in invoice.items" :key="item.id" class="border-b border-cyan-500/5">
                            <td class="px-4 py-3 text-slate-500">{{ i + 1 }}</td>
                            <td class="px-4 py-3">{{ item.description }}</td>
                            <td class="px-4 py-3">{{ Math.round(Number(item.quantity)) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ item.unit }}</td>
                            <td class="px-4 py-3 font-mono">{{ formatMoney(item.unit_price_aed) }}</td>
                            <td class="px-4 py-3 font-mono text-amber-400">{{ formatMoney(item.total_aed) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-amber-500/15 bg-amber-500/5 p-5">
                    <h2 class="mb-3 text-sm font-semibold text-amber-400">💰 {{ t('ui.grand_total') }}</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-slate-400">{{ t('ui.subtotal') }}</span><span class="font-mono">{{ formatMoney(invoice.subtotal_aed) }} AED</span></div>
                        <div class="flex justify-between"><span class="text-slate-400">{{ t('ui.discount') }}</span><span class="font-mono">{{ formatMoney(invoice.discount_aed) }} AED</span></div>
                        <div class="flex justify-between"><span class="text-slate-400">{{ t('ui.after_discount') }}</span><span class="font-mono">{{ formatMoney(afterDiscount) }} AED</span></div>
                        <div class="flex justify-between"><span class="text-slate-400">{{ t('ui.vat') }} 5%</span><span class="font-mono">{{ formatMoney(invoice.vat_aed) }} AED</span></div>
                        <div class="flex justify-between border-t border-amber-500/20 pt-2 text-lg font-bold text-amber-400">
                            <span>{{ t('ui.total') }}</span><span class="font-mono">{{ formatMoney(invoice.total_aed) }} AED</span>
                        </div>
                    </div>
                </div>
                <div v-if="invoice.notes" class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <h2 class="mb-2 text-sm font-semibold text-slate-300">{{ t('ui.notes') }}</h2>
                    <p class="text-sm text-slate-400">{{ invoice.notes }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
