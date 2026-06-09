<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    invoices: Object,
    filters: Object,
    types: Array,
    statuses: Array,
    contacts: Array,
    projects: Array,
    products: Array,
    previewNumber: String,
    defaultType: String,
    today: String,
});

const { t, locale, displayName, formatMoney } = useLocale();
const mainTab = ref('new');
const docNumber = ref(props.previewNumber);
const itemMode = ref('catalog');
const search = ref(props.filters?.search || '');
const filterType = ref(props.filters?.type || '');

const form = useForm({
    type: props.defaultType || 'quotation',
    document_date: props.today,
    contact_id: '',
    project_id: '',
    client_name: '',
    attention_to: '',
    project_name: '',
    consultant: '',
    main_contractor: '',
    mep_contractor: '',
    phone: '',
    email: '',
    lpo_number: '',
    address: '',
    discount_aed: 0,
    valid_until: '',
    notes: '',
    items: [{ product_id: '', description: '', quantity: 1, unit: 'pcs', unit_price_aed: 0 }],
});

const typeLabel = computed(() => {
    const typeItem = props.types.find((x) => x.value === form.type);
    return locale.value === 'ar' ? typeItem?.label_ar : typeItem?.label_en;
});

const subtotal = computed(() =>
    form.items.reduce((sum, item) => sum + (Number(item.quantity) || 0) * (Number(item.unit_price_aed) || 0), 0)
);
const afterDiscount = computed(() => Math.max(0, subtotal.value - (Number(form.discount_aed) || 0)));
const vat = computed(() => Math.round(afterDiscount.value * 0.05 * 100) / 100);
const total = computed(() => Math.round((afterDiscount.value + vat.value) * 100) / 100);

watch(() => form.type, async (type) => {
    const res = await fetch(`/invoices/preview-number?type=${type}`);
    const data = await res.json();
    docNumber.value = data.number;
});

function typeLabelOf(value) {
    const typeItem = props.types.find((x) => x.value === value);
    return locale.value === 'ar' ? typeItem?.label_ar : typeItem?.label_en;
}

function statusLabel(value) {
    return props.statuses.find((s) => s.value === value)?.label || value;
}

function onContactChange() {
    const c = props.contacts.find((x) => x.id === form.contact_id);
    if (!c) return;
    form.client_name = displayName(c);
    form.phone = c.phone || '';
    form.email = c.email || '';
}

function onProjectChange() {
    const p = props.projects.find((x) => x.id === form.project_id);
    if (!p) return;
    form.project_name = displayName(p);
    form.consultant = p.consultant ? displayName(p.consultant) : '';
    form.main_contractor = p.contractor ? displayName(p.contractor) : '';
}

function addFromCatalog() {
    form.items.push({ product_id: '', description: '', quantity: 1, unit: 'pcs', unit_price_aed: 0 });
}

function addManual() {
    form.items.push({ product_id: '', description: '', quantity: 1, unit: 'pcs', unit_price_aed: 0 });
    itemMode.value = 'manual';
}

function removeItem(index) {
    if (form.items.length > 1) form.items.splice(index, 1);
}

function onProductSelect(index) {
    const item = form.items[index];
    const p = props.products.find((x) => x.id === item.product_id);
    if (!p) return;
    item.description = displayName(p);
    item.unit = p.unit || 'pcs';
    item.unit_price_aed = Number(p.price_aed);
}

function lineTotal(item) {
    return ((Number(item.quantity) || 0) * (Number(item.unit_price_aed) || 0)).toFixed(2);
}

function submit() {
    form.post('/invoices', {
        onSuccess: () => {
            form.reset();
            form.type = props.defaultType;
            form.document_date = props.today;
            form.items = [{ product_id: '', description: '', quantity: 1, unit: 'pcs', unit_price_aed: 0 }];
            mainTab.value = 'saved';
        },
    });
}

function applyFilters() {
    router.get('/invoices', {
        search: search.value || undefined,
        type: filterType.value || undefined,
    }, { preserveState: true });
}

const typeColors = {
    quotation: 'border-amber-500/40 bg-amber-500/10 text-amber-400',
    sales: 'border-cyan-500/40 bg-cyan-500/10 text-cyan-400',
    proforma: 'border-violet-500/40 bg-violet-500/10 text-violet-400',
};

const inputCls = 'w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400';
</script>


<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">{{ t('invoices.title') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ t('invoices.flowguard_materials') }}</p>
            </div>

            <!-- Main tabs -->
            <div class="mb-6 flex gap-2 border-b border-cyan-500/15 pb-3">
                <button
                    @click="mainTab = 'new'"
                    class="rounded-lg px-5 py-2.5 text-sm font-semibold transition"
                    :class="mainTab === 'new' ? 'bg-amber-500 text-[#0a0f1e]' : 'text-slate-400 hover:text-white'"
                >
                    📄 {{ t('invoices.new') }}
                </button>
                <button
                    @click="mainTab = 'saved'"
                    class="rounded-lg px-5 py-2.5 text-sm font-semibold transition"
                    :class="mainTab === 'saved' ? 'bg-amber-500 text-[#0a0f1e]' : 'text-slate-400 hover:text-white'"
                >
                    💾 {{ t('invoices.saved') }}
                    <span class="mr-1 rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ invoices.total }}</span>
                </button>
            </div>

            <!-- New Invoice -->
            <div v-if="mainTab === 'new'" class="space-y-6">
                <!-- Type tabs -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="typeOption in types"
                        :key="typeOption.value"
                        @click="form.type = typeOption.value"
                        class="rounded-xl border px-4 py-2.5 text-sm font-medium transition"
                        :class="form.type === typeOption.value ? typeColors[typeOption.value] : 'border-cyan-500/15 text-slate-400 hover:border-cyan-500/30'"
                    >
                        {{ locale === 'ar' ? typeOption.label_ar : typeOption.label_en }}
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Document header -->
                    <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-lg font-bold text-amber-400">{{ typeLabel }}</h2>
                            <div class="text-left">
                                <p class="text-xs text-slate-500">{{ t('ui.number') }}</p>
                                <p class="font-mono text-lg font-bold text-cyan-400">{{ docNumber }}</p>
                            </div>
                        </div>

                        <!-- Client & Project -->
                        <div class="mb-4 rounded-lg border border-cyan-500/10 bg-[#0f172a]/50 p-4">
                            <h3 class="mb-3 text-sm font-semibold text-slate-300">👤 {{ t('ui.customer') }} / {{ t('ui.project') }}</h3>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('invoices.document_date') }}</label>
                                    <input v-model="form.document_date" type="date" required :class="inputCls" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.select_contact') }}</label>
                                    <select v-model="form.contact_id" @change="onContactChange" :class="inputCls">
                                        <option value="">— {{ t('ui.optional') }} —</option>
                                        <option v-for="c in contacts" :key="c.id" :value="c.id">{{ displayName(c) }} {{ c.company ? `(${c.company})` : '' }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.select_project') }}</label>
                                    <select v-model="form.project_id" @change="onProjectChange" :class="inputCls">
                                        <option value="">— {{ t('ui.optional') }} —</option>
                                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ displayName(p) }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.client_name') }}</label>
                                    <input v-model="form.client_name" :placeholder="t('ui.client_name')" :class="inputCls" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.attention_to') }}</label>
                                    <input v-model="form.attention_to" :placeholder="t('ui.attention_to')" :class="inputCls" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.project_name') }}</label>
                                    <input v-model="form.project_name" :placeholder="t('ui.project_name')" :class="inputCls" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.consultant') }}</label>
                                    <input v-model="form.consultant" :class="inputCls" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.contractor') }}</label>
                                    <input v-model="form.main_contractor" :class="inputCls" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.mep_contractor') }}</label>
                                    <input v-model="form.mep_contractor" :class="inputCls" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.phone') }}</label>
                                    <input v-model="form.phone" placeholder="+971 ..." :class="inputCls" dir="ltr" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.email') }}</label>
                                    <input v-model="form.email" type="email" placeholder="email@example.com" :class="inputCls" dir="ltr" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.lpo_number') }}</label>
                                    <input v-model="form.lpo_number" :placeholder="t('ui.lpo_number')" :class="inputCls" />
                                </div>
                                <div class="md:col-span-2 lg:col-span-3">
                                    <label class="mb-1 block text-xs text-slate-500">{{ t('ui.address') }}</label>
                                    <input v-model="form.address" :placeholder="t('ui.address')" :class="inputCls" />
                                </div>
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="mb-4 rounded-lg border border-cyan-500/10 bg-[#0f172a]/50 p-4">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold text-slate-300">📋 {{ t('ui.items') }}</h3>
                                <div class="flex gap-2">
                                    <button type="button" @click="itemMode = 'catalog'; addFromCatalog()" class="rounded-lg border border-cyan-500/20 px-3 py-1.5 text-xs text-cyan-400 hover:bg-cyan-500/10">
                                        📦 {{ t('catalog.select_catalog') }}
                                    </button>
                                    <button type="button" @click="addManual()" class="rounded-lg border border-amber-500/20 px-3 py-1.5 text-xs text-amber-400 hover:bg-amber-500/10">
                                        ➕ {{ t('ui.add') }}
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-cyan-500/10 text-start text-xs text-slate-500">
                                            <th class="px-2 py-2 w-8">#</th>
                                            <th class="px-2 py-2 min-w-[200px]">{{ t('ui.description') }}</th>
                                            <th class="px-2 py-2 w-20">{{ t('ui.quantity') }}</th>
                                            <th class="px-2 py-2 w-20">{{ t('ui.unit') }}</th>
                                            <th class="px-2 py-2 w-28">{{ t('ui.price_aed') }}</th>
                                            <th class="px-2 py-2 w-28">{{ t('ui.total') }} (AED)</th>
                                            <th class="px-2 py-2 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index" class="border-b border-cyan-500/5">
                                            <td class="px-2 py-2 text-slate-500">{{ index + 1 }}</td>
                                            <td class="px-2 py-2">
                                                <select
                                                    v-if="itemMode === 'catalog' && !item.description"
                                                    v-model="item.product_id"
                                                    @change="onProductSelect(index)"
                                                    :class="inputCls + ' text-xs'"
                                                >
                                                    <option value="">{{ t('ui.select_product') }}...</option>
                                                    <option v-for="p in products" :key="p.id" :value="p.id">
                                                        {{ p.sku }} — {{ displayName(p) }}
                                                    </option>
                                                </select>
                                                <input v-model="item.description" :placeholder="t('ui.description')" :class="inputCls + ' text-xs'" required />
                                            </td>
                                            <td class="px-2 py-2">
                                                <input v-model.number="item.quantity" type="number" step="1" min="1" :class="inputCls + ' text-xs'" required />
                                            </td>
                                            <td class="px-2 py-2">
                                                <input v-model="item.unit" :class="inputCls + ' text-xs'" />
                                            </td>
                                            <td class="px-2 py-2">
                                                <input v-model="item.unit_price_aed" type="number" step="0.01" min="0" :class="inputCls + ' text-xs'" required />
                                            </td>
                                            <td class="px-2 py-2 font-mono text-xs text-amber-400">{{ lineTotal(item) }}</td>
                                            <td class="px-2 py-2">
                                                <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="text-red-400 text-xs">✕</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Summary + Options side by side -->
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="rounded-lg border border-amber-500/15 bg-amber-500/5 p-4">
                                <h3 class="mb-3 text-sm font-semibold text-amber-400">💰 {{ t('ui.grand_total') }}</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between"><span class="text-slate-400">{{ t('ui.subtotal') }}</span><span class="font-mono">{{ subtotal.toFixed(2) }} AED</span></div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-slate-400">{{ t('ui.discount') }} (AED)</span>
                                        <input v-model="form.discount_aed" type="number" step="0.01" min="0" class="w-28 rounded border border-cyan-500/20 bg-[#0f172a] px-2 py-1 text-left font-mono text-sm outline-none" />
                                    </div>
                                    <div class="flex justify-between"><span class="text-slate-400">{{ t('ui.after_discount') }}</span><span class="font-mono">{{ afterDiscount.toFixed(2) }} AED</span></div>
                                    <div class="flex justify-between"><span class="text-slate-400">{{ t('ui.vat') }} 5%</span><span class="font-mono">{{ vat.toFixed(2) }} AED</span></div>
                                    <div class="flex justify-between border-t border-amber-500/20 pt-2 text-base font-bold text-amber-400">
                                        <span>{{ t('ui.total') }}</span><span class="font-mono">{{ total.toFixed(2) }} AED</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-cyan-500/10 bg-[#0f172a]/50 p-4">
                                <h3 class="mb-3 text-sm font-semibold text-slate-300">⚙️ {{ t('ui.optional') }}</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-500">{{ t('ui.notes') }}</label>
                                        <textarea v-model="form.notes" rows="2" :placeholder="t('ui.notes') + '...'" :class="inputCls + ' resize-none'" />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-500">{{ t('ui.valid_until') }}</label>
                                        <input v-model="form.valid_until" type="date" :class="inputCls" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-amber-500 px-6 py-2.5 text-sm font-bold text-[#0a0f1e] hover:bg-amber-400 disabled:opacity-50">
                            💾 {{ t('ui.save') }}
                        </button>
                        <span class="self-center text-xs text-slate-500">{{ t('ui.preview') }} PDF + {{ t('ui.export_excel') }}</span>
                    </div>
                </form>
            </div>

            <!-- Saved Invoices -->
            <div v-if="mainTab === 'saved'">
                <div class="mb-4 flex flex-wrap gap-3">
                    <input v-model="search" @keyup.enter="applyFilters" :placeholder="t('ui.search')" :class="inputCls + ' w-56'" />
                    <select v-model="filterType" @change="applyFilters" :class="inputCls + ' w-44'">
                        <option value="">{{ t('crm.all_types') }}</option>
                        <option v-for="typeOption in types" :key="typeOption.value" :value="typeOption.value">{{ locale === 'ar' ? typeOption.label_ar : typeOption.label_en }}</option>
                    </select>
                </div>

                <div class="overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-4 py-3 font-medium">{{ t('ui.number') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.type') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.customer') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.project') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.date') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.total') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.status') }}</th>
                                <th class="px-4 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inv in invoices.data" :key="inv.id" class="border-b border-cyan-500/5 hover:bg-white/[0.02]">
                                <td class="px-4 py-3 font-mono text-cyan-400">
                                    {{ inv.number }}
                                    <span v-if="inv.source === 'website'" class="ms-2 rounded bg-orange-500/20 px-1.5 py-0.5 text-[10px] text-orange-300">🌐</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs" :class="typeColors[inv.type]">{{ typeLabelOf(inv.type) }}</span>
                                </td>
                                <td class="px-4 py-3">{{ inv.client_name || (inv.contact ? displayName(inv.contact) : '—') }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ inv.project_name || (inv.project ? displayName(inv.project) : '—') }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ inv.document_date }}</td>
                                <td class="px-4 py-3 font-medium text-amber-400">{{ formatMoney(inv.total_aed) }} AED</td>
                                <td class="px-4 py-3 text-xs text-slate-400">{{ statusLabel(inv.status) }}</td>
                                <td class="px-4 py-3">
                                    <Link :href="`/invoices/${inv.id}`" class="text-xs text-cyan-400 hover:text-cyan-300">{{ t('ui.view') }}</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="!invoices.data?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_invoices') }}</p>
            </div>
        </div>
    </AppLayout>
</template>

