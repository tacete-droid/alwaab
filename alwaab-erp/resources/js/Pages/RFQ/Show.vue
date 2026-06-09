<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    rfq: Object,
    statuses: Array,
});

const page = usePage();
const { t, displayName, formatMoney, formatDate } = useLocale();
const permissions = computed(() => page.props.auth?.user?.permissions || []);
const fileInput = ref(null);

const boqForm = useForm({
    boq_file: null,
});

function statusLabel(value) {
    return props.statuses.find((s) => s.value === value)?.label || value;
}

function onFileChange(event) {
    boqForm.boq_file = event.target.files[0] || null;
}

function uploadBoq() {
    if (!boqForm.boq_file) return;
    boqForm.post(`/rfqs/${props.rfq.id}/boq`, {
        forceFormData: true,
        onSuccess: () => {
            boqForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

function createQuotation() {
    if (!confirm(t('ui.confirm_quotation_boq'))) return;
    router.post(`/rfqs/${props.rfq.id}/quotation`);
}

const statusColors = {
    pending: 'bg-amber-500/20 text-amber-300',
    processing: 'bg-cyan-500/20 text-cyan-300',
    quoted: 'bg-green-500/20 text-green-300',
    closed: 'bg-slate-500/20 text-slate-400',
};

const boqItems = computed(() => props.rfq.boq_items || []);
const canCreateQuotation = computed(() =>
    permissions.value.includes('quotations.create')
    && boqItems.value.length > 0
    && !['quoted', 'closed'].includes(props.rfq.status),
);
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-6xl">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <Link href="/rfqs" class="mb-2 inline-block text-xs text-slate-500 hover:text-cyan-400">← {{ t('ui.back_to_rfqs') }}</Link>
                    <h1 class="text-2xl font-bold font-mono text-purple-400">{{ rfq.number }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ displayName(rfq.contact) }} — {{ rfq.contact?.company }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span v-if="rfq.source === 'website'" class="rounded-full bg-orange-500/20 px-3 py-1 text-xs font-medium text-orange-300">
                        🌐 {{ t('quotations.source_website') }}
                    </span>
                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="statusColors[rfq.status]">
                        {{ statusLabel(rfq.status) }}
                    </span>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.customer') }}</p>
                    <p class="mt-1 text-sm">{{ displayName(rfq.contact) }}</p>
                    <p v-if="rfq.contact?.email" class="mt-1 text-xs text-cyan-400">{{ rfq.contact.email }}</p>
                    <p v-if="rfq.contact?.phone" class="text-xs text-slate-500">{{ rfq.contact.phone }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.project') }}</p>
                    <p class="mt-1 text-sm">{{ rfq.project ? displayName(rfq.project) : '—' }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.assignee') }}</p>
                    <p class="mt-1 text-sm">{{ rfq.assignee ? displayName(rfq.assignee) : '—' }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.date') }}</p>
                    <p class="mt-1 text-sm">{{ formatDate(rfq.created_at) }}</p>
                </div>
            </div>

            <p v-if="rfq.description" class="mb-6 rounded-xl border border-cyan-500/10 bg-[#1a2540] p-4 text-sm text-slate-300">
                {{ rfq.description }}
            </p>

            <div v-if="permissions.includes('quotations.create')" class="mb-6 rounded-xl border border-purple-500/15 bg-[#1a2540] p-6">
                <h2 class="mb-1 font-semibold text-purple-400">{{ t('quotations.boq_upload') }}</h2>
                <p class="mb-4 text-xs text-slate-500">{{ t('quotations.boq_upload_hint') }}</p>
                <form @submit.prevent="uploadBoq" class="flex flex-wrap items-end gap-3">
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        class="text-sm text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-purple-500/20 file:px-4 file:py-2 file:text-sm file:text-purple-300"
                        @change="onFileChange"
                    />
                    <button
                        type="submit"
                        :disabled="!boqForm.boq_file || boqForm.processing"
                        class="rounded-lg bg-purple-500 px-5 py-2 text-sm font-bold text-white hover:bg-purple-400 disabled:opacity-50"
                    >
                        {{ boqForm.processing ? t('ui.parsing') : t('ui.upload') }}
                    </button>
                </form>
                <p v-if="boqForm.errors.boq_file" class="mt-2 text-xs text-red-400">{{ boqForm.errors.boq_file }}</p>
                <a
                    v-if="rfq.boq_file_url"
                    :href="rfq.boq_file_url"
                    target="_blank"
                    class="mt-3 inline-block text-xs text-cyan-400 hover:underline"
                >
                    {{ t('ui.view_file') }}
                </a>
            </div>

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-semibold text-cyan-400">{{ t('quotations.boq_items') }} ({{ boqItems.length }})</h2>
                <button
                    v-if="canCreateQuotation"
                    @click="createQuotation"
                    class="rounded-lg bg-amber-500 px-5 py-2 text-sm font-bold text-[#0a0f1e] hover:bg-amber-400"
                >
                    {{ t('quotations.create_quotation_from_boq') }}
                </button>
            </div>

            <div v-if="boqItems.length" class="overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-3 py-3 font-medium">#</th>
                            <th class="px-3 py-3 font-medium">{{ t('ui.description') }}</th>
                            <th class="px-3 py-3 font-medium">{{ t('ui.client_enquiry') }}</th>
                            <th class="px-3 py-3 font-medium">{{ t('ui.quantity') }}</th>
                            <th class="px-3 py-3 font-medium">{{ t('ui.unit') }}</th>
                            <th class="px-3 py-3 font-medium">{{ t('ui.unit_price') }}</th>
                            <th class="px-3 py-3 font-medium">{{ t('ui.total') }}</th>
                            <th class="px-3 py-3 font-medium">{{ t('ui.catalog_match') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in boqItems"
                            :key="item.id"
                            class="border-b border-cyan-500/5 hover:bg-white/[0.02]"
                        >
                            <td class="px-3 py-2 text-slate-500">{{ item.sort_order }}</td>
                            <td class="px-3 py-2">
                                <span v-if="item.category" class="mb-0.5 block text-[10px] text-purple-400">{{ item.category }}</span>
                                {{ item.description }}
                            </td>
                            <td class="px-3 py-2 font-mono text-xs text-cyan-400">{{ item.client_enquiry || '—' }}</td>
                            <td class="px-3 py-2">{{ item.quantity }}</td>
                            <td class="px-3 py-2 text-slate-400">{{ item.unit }}</td>
                            <td class="px-3 py-2">{{ formatMoney(item.unit_price_aed) }}</td>
                            <td class="px-3 py-2 font-medium text-amber-400">{{ formatMoney(item.total_aed) }}</td>
                            <td class="px-3 py-2">
                                <span
                                    v-if="item.product"
                                    class="rounded-full bg-green-500/20 px-2 py-0.5 text-[10px] text-green-300"
                                    :title="item.product.sku"
                                >
                                    {{ item.product.sku }}
                                </span>
                                <span v-else class="rounded-full bg-amber-500/20 px-2 py-0.5 text-[10px] text-amber-300">{{ t('ui.unmatched') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="rounded-xl border border-dashed border-cyan-500/20 py-12 text-center text-slate-500">
                {{ t('ui.no_boq_yet') }}
            </p>

            <div v-if="rfq.quotations?.length" class="mt-8">
                <h2 class="mb-3 font-semibold text-amber-400">{{ t('ui.linked_quotations') }}</h2>
                <div class="space-y-2">
                    <Link
                        v-for="q in rfq.quotations"
                        :key="q.id"
                        :href="`/quotations/${q.id}`"
                        class="flex items-center justify-between rounded-lg border border-amber-500/15 bg-[#1a2540] px-4 py-3 text-sm hover:bg-white/[0.02]"
                    >
                        <span class="font-mono text-amber-400">{{ q.number }}</span>
                        <span class="text-slate-400">{{ formatMoney(q.total_aed) }} AED</span>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
