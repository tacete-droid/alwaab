<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Object,
    filters: Object,
    types: Array,
    catalogTree: Array,
    parentCategory: Object,
    warehouses: Array,
});

const page = usePage();
const { t, displayName, formatMoney, formatNumber } = useLocale();
const permissions = computed(() => page.props.auth?.user?.permissions || []);
const canUpdatePrice = computed(() => permissions.value.includes('products.update'));
const canViewStock = computed(() => permissions.value.includes('inventory.view'));
const canManageStock = computed(() => permissions.value.includes('inventory.manage'));

const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');
const categoryId = ref(props.filters?.category_id || '');
const warehouseId = ref(props.filters?.warehouse_id || '');
const lowStock = ref(props.filters?.low_stock || false);

const editingPriceId = ref(null);
const editingStockId = ref(null);
const priceForm = useForm({ price_aed: '', price_with_markup_aed: '' });
const stockForm = useForm({ qty_on_hand: '', reorder_point: '' });

function applyFilters() {
    router.get('/catalog/products', {
        search: search.value || undefined,
        type: type.value || undefined,
        category_id: categoryId.value || undefined,
        warehouse_id: warehouseId.value || undefined,
        low_stock: lowStock.value || undefined,
    }, { preserveState: true });
}

function typeLabel(value) {
    return props.types.find((item) => item.value === value)?.label || value;
}

function categoryLabel(cat) {
    if (!cat) return '—';
    return displayName(cat);
}

function stockRow(product) {
    return product.inventory?.[0] || null;
}

function isLowStock(product) {
    const row = stockRow(product);
    return row && Number(row.qty_on_hand) <= Number(row.reorder_point);
}

function startPriceEdit(product) {
    editingStockId.value = null;
    editingPriceId.value = product.id;
    priceForm.price_aed = product.price_aed;
    priceForm.price_with_markup_aed = product.price_with_markup_aed;
}

function startStockEdit(product) {
    const row = stockRow(product);
    if (!row) return;
    editingPriceId.value = null;
    editingStockId.value = row.id;
    stockForm.qty_on_hand = row.qty_on_hand;
    stockForm.reorder_point = row.reorder_point;
}

function cancelEdit() {
    editingPriceId.value = null;
    editingStockId.value = null;
    priceForm.reset();
    stockForm.reset();
}

function savePrice(product) {
    priceForm.put(`/catalog/products/${product.id}/price`, {
        preserveScroll: true,
        onSuccess: cancelEdit,
    });
}

function saveStock() {
    stockForm.put(`/inventory/${editingStockId.value}/stock`, {
        preserveScroll: true,
        onSuccess: cancelEdit,
    });
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">{{ t('catalog.catalog_and_stock') }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ parentCategory ? displayName(parentCategory) : 'FlowGuard' }} — {{ t('ui.count', { count: products.total }) }}
                </p>
            </div>

            <div class="mb-6 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                <label class="mb-2 block text-sm font-medium text-cyan-400">{{ t('catalog.select_catalog') }}</label>
                <div class="flex flex-wrap gap-3">
                    <select
                        v-model="categoryId"
                        @change="applyFilters"
                        class="min-w-[220px] flex-1 rounded-lg border border-cyan-500/30 bg-[#0f172a] px-4 py-2.5 text-sm font-medium outline-none focus:border-cyan-400"
                    >
                        <option value="">{{ t('catalog.all_sections') }}</option>
                        <option v-for="cat in catalogTree" :key="cat.id" :value="cat.id">
                            {{ categoryLabel(cat) }}
                        </option>
                    </select>
                    <select
                        v-model="type"
                        @change="applyFilters"
                        class="w-40 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2.5 text-sm outline-none focus:border-cyan-400"
                    >
                        <option value="">{{ t('crm.all_types') }}</option>
                        <option v-for="item in types" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select>
                    <select
                        v-if="canViewStock"
                        v-model="warehouseId"
                        @change="applyFilters"
                        class="w-48 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2.5 text-sm outline-none focus:border-cyan-400"
                    >
                        <option value="">{{ t('ui.warehouse') }}</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ displayName(w) }}</option>
                    </select>
                </div>
            </div>

            <div class="mb-4 flex flex-wrap gap-3">
                <input
                    v-model="search"
                    @keyup.enter="applyFilters"
                    :placeholder="t('ui.search')"
                    class="w-56 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400"
                />
                <label v-if="canViewStock" class="flex items-center gap-2 text-sm text-slate-400">
                    <input v-model="lowStock" type="checkbox" @change="applyFilters" class="rounded" />
                    {{ t('reports.low_stock') }}
                </label>
                <button
                    @click="applyFilters"
                    class="rounded-lg border border-cyan-500/30 px-4 py-2 text-sm text-cyan-400 hover:bg-cyan-500/10"
                >
                    {{ t('ui.search') }}
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-4 py-3 font-medium">#</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.sku') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.description') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.category') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.type') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.size') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.unit') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.price') }}</th>
                            <th v-if="canViewStock" class="px-4 py-3 font-medium">{{ t('ui.quantity') }}</th>
                            <th v-if="canViewStock" class="px-4 py-3 font-medium">{{ t('ui.stock') }}</th>
                            <th v-if="canViewStock" class="px-4 py-3 font-medium">{{ t('ui.reorder_point') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.edit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="border-b border-cyan-500/5 hover:bg-white/[0.02]"
                            :class="isLowStock(product) ? 'bg-red-500/5' : ''"
                        >
                            <td class="px-4 py-3 text-slate-500">{{ product.source_sno || '—' }}</td>
                            <td class="px-4 py-3 font-mono text-cyan-400">{{ product.sku }}</td>
                            <td class="max-w-xs truncate px-4 py-3" :title="displayName(product)">{{ displayName(product) }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ product.category ? categoryLabel(product.category) : '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-cyan-500/10 px-2 py-0.5 text-xs text-cyan-300">
                                    {{ product.fitting_type || typeLabel(product.type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ product.size || '—' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ product.unit }}</td>
                            <td class="px-4 py-3">
                                <input
                                    v-if="editingPriceId === product.id"
                                    v-model="priceForm.price_aed"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-24 rounded border border-cyan-500/30 bg-[#0f172a] px-2 py-1 text-sm outline-none focus:border-cyan-400"
                                />
                                <span v-else class="font-medium text-amber-400">{{ formatMoney(product.price_aed) }}</span>
                            </td>
                            <template v-if="canViewStock">
                                <td class="px-4 py-3">
                                    <input
                                        v-if="editingStockId === stockRow(product)?.id"
                                        v-model="stockForm.qty_on_hand"
                                        type="number"
                                        step="0.001"
                                        min="0"
                                        class="w-24 rounded border border-cyan-500/30 bg-[#0f172a] px-2 py-1 text-sm outline-none focus:border-cyan-400"
                                    />
                                    <span v-else class="font-medium" :class="isLowStock(product) ? 'text-red-400' : 'text-green-400'">
                                        {{ stockRow(product) ? formatNumber(stockRow(product).qty_on_hand) : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400">
                                    {{ stockRow(product) ? formatNumber(stockRow(product).qty_on_hand - stockRow(product).qty_reserved) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        v-if="editingStockId === stockRow(product)?.id"
                                        v-model="stockForm.reorder_point"
                                        type="number"
                                        step="0.001"
                                        min="0"
                                        class="w-24 rounded border border-cyan-500/30 bg-[#0f172a] px-2 py-1 text-sm outline-none focus:border-cyan-400"
                                    />
                                    <span v-else class="text-slate-400">
                                        {{ stockRow(product) ? formatNumber(stockRow(product).reorder_point) : '—' }}
                                    </span>
                                </td>
                            </template>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <template v-if="editingPriceId === product.id">
                                    <button @click="savePrice(product)" :disabled="priceForm.processing" class="text-xs text-green-400">{{ t('ui.save') }}</button>
                                    <button @click="cancelEdit" class="mr-2 text-xs text-slate-400">{{ t('ui.cancel') }}</button>
                                </template>
                                <template v-else-if="editingStockId === stockRow(product)?.id">
                                    <button @click="saveStock" :disabled="stockForm.processing" class="text-xs text-green-400">{{ t('ui.save') }}</button>
                                    <button @click="cancelEdit" class="mr-2 text-xs text-slate-400">{{ t('ui.cancel') }}</button>
                                </template>
                                <template v-else>
                                    <button
                                        v-if="canUpdatePrice"
                                        @click="startPriceEdit(product)"
                                        class="text-xs text-amber-400 hover:text-amber-300"
                                    >
                                        {{ t('ui.price') }}
                                    </button>
                                    <button
                                        v-if="canManageStock && stockRow(product)"
                                        @click="startStockEdit(product)"
                                        class="mr-2 text-xs text-cyan-400 hover:text-cyan-300"
                                    >
                                        {{ t('ui.quantity') }}
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="products.last_page > 1" class="mt-4 flex justify-center gap-2">
                <button
                    v-for="link in products.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="link.url && router.get(link.url)"
                    class="rounded px-3 py-1 text-sm"
                    :class="link.active
                        ? 'bg-cyan-500 text-[#0a0f1e] font-bold'
                        : link.url
                            ? 'text-slate-400 hover:bg-white/5'
                            : 'text-slate-600 cursor-not-allowed'"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>
