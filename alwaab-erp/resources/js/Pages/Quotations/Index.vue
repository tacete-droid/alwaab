<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    quotations: Object,
    filters: Object,
    statuses: Array,
    contacts: Array,
    projects: Array,
    rfqs: Array,
    products: Array,
});

const { t, displayName, formatMoney } = useLocale();
const showForm = ref(false);
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const form = useForm({
    contact_id: '',
    project_id: '',
    rfq_id: '',
    discount_aed: 0,
    notes: '',
    items: [{ product_id: '', quantity: 1 }],
});

function statusLabel(value) {
    return props.statuses.find((s) => s.value === value)?.label || value;
}

function applyFilters() {
    router.get('/quotations', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
}

function addItem() {
    form.items.push({ product_id: '', quantity: 1 });
}

function removeItem(index) {
    if (form.items.length > 1) form.items.splice(index, 1);
}

function productPrice(productId) {
    const p = props.products.find((x) => x.id === productId);
    return p ? Number(p.price_aed) : 0;
}

function submit() {
    form.post('/quotations', { onSuccess: () => { showForm.value = false; form.reset(); form.items = [{ product_id: '', quantity: 1 }]; } });
}

const statusColors = {
    draft: 'text-slate-400',
    sent: 'text-cyan-400',
    approved: 'text-green-400',
    rejected: 'text-red-400',
    expired: 'text-amber-400',
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ t('quotations.quotations') }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ t('ui.count', { count: quotations.total }) }}</p>
                </div>
                <button
                    @click="showForm = !showForm"
                    class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-[#0a0f1e] hover:bg-amber-400"
                >
                    + {{ t('quotations.new_quotation') }}
                </button>
            </div>

            <div v-if="showForm" class="mb-6 rounded-xl border border-amber-500/15 bg-[#1a2540] p-6">
                <h2 class="mb-4 font-semibold text-amber-400">{{ t('quotations.new_quotation') }}</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <select v-model="form.contact_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required>
                            <option value="">{{ t('ui.customer') }} *</option>
                            <option v-for="c in contacts" :key="c.id" :value="c.id">{{ displayName(c) }}</option>
                        </select>
                        <select v-model="form.project_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                            <option value="">{{ t('ui.project') }}</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ displayName(p) }}</option>
                        </select>
                        <select v-model="form.rfq_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                            <option value="">{{ t('quotations.rfqs') }}</option>
                            <option v-for="r in rfqs" :key="r.id" :value="r.id">{{ r.number }}</option>
                        </select>
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-medium text-slate-300">{{ t('ui.items') }}</p>
                        <div v-for="(item, index) in form.items" :key="index" class="mb-2 flex flex-wrap items-center gap-2">
                            <select v-model="item.product_id" class="min-w-[200px] flex-1 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required>
                                <option value="">{{ t('ui.product') }} *</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">
                                    {{ p.sku }} — {{ displayName(p) }} ({{ formatMoney(p.price_aed) }} AED)
                                </option>
                            </select>
                            <input v-model="item.quantity" type="number" step="0.001" min="0.001" :placeholder="t('ui.quantity')" class="w-24 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                            <span v-if="item.product_id" class="text-xs text-slate-500">
                                ≈ {{ formatMoney(productPrice(item.product_id) * item.quantity) }} AED
                            </span>
                            <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="text-xs text-red-400">{{ t('ui.delete') }}</button>
                        </div>
                        <button type="button" @click="addItem" class="text-xs text-cyan-400 hover:text-cyan-300">+ {{ t('ui.add_item') }}</button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <input v-model="form.discount_aed" type="number" step="0.01" min="0" :placeholder="t('ui.discount') + ' (AED)'" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                        <input v-model="form.notes" :placeholder="t('ui.notes')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-amber-500 px-6 py-2 text-sm font-bold text-[#0a0f1e]">{{ t('ui.save') }}</button>
                        <button type="button" @click="showForm = false" class="text-sm text-slate-400">{{ t('ui.cancel') }}</button>
                    </div>
                </form>
            </div>

            <div class="mb-4 flex flex-wrap gap-3">
                <input v-model="search" @keyup.enter="applyFilters" :placeholder="t('ui.search')" class="w-48 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                <select v-model="status" @change="applyFilters" class="w-40 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                    <option value="">{{ t('crm.all_statuses') }}</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>

            <div class="overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-4 py-3 font-medium">{{ t('ui.number') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.customer') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.project') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.total') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.status') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.version') }}</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="q in quotations.data" :key="q.id" class="border-b border-cyan-500/5 hover:bg-white/[0.02]">
                            <td class="px-4 py-3 font-mono text-amber-400">{{ q.number }}</td>
                            <td class="px-4 py-3">{{ displayName(q.contact) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ q.project ? displayName(q.project) : '—' }}</td>
                            <td class="px-4 py-3 font-medium">{{ formatMoney(q.total_aed) }} AED</td>
                            <td class="px-4 py-3">
                                <span class="text-xs" :class="statusColors[q.status]">{{ statusLabel(q.status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">v{{ q.version }}</td>
                            <td class="px-4 py-3">
                                <Link :href="`/quotations/${q.id}`" class="text-xs text-cyan-400 hover:text-cyan-300">{{ t('ui.view') }}</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p v-if="!quotations.data?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_quotations') }}</p>
        </div>
    </AppLayout>
</template>
