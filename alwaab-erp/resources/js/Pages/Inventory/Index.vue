<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stock: Object,
    filters: Object,
    warehouses: Array,
});

const page = usePage();
const { t, displayName, formatNumber } = useLocale();
const canManage = computed(() => page.props.auth?.user?.permissions?.includes('inventory.manage'));

const search = ref(props.filters?.search || '');
const warehouseId = ref(props.filters?.warehouse_id || '');
const lowStock = ref(props.filters?.low_stock || false);

const editingId = ref(null);
const stockForm = useForm({ qty_on_hand: '', reorder_point: '' });

function applyFilters() {
    router.get('/inventory', {
        search: search.value || undefined,
        warehouse_id: warehouseId.value || undefined,
        low_stock: lowStock.value || undefined,
    }, { preserveState: true });
}

function availableQty(row) {
    return Number(row.qty_on_hand) - Number(row.qty_reserved);
}

function startEdit(row) {
    editingId.value = row.id;
    stockForm.qty_on_hand = row.qty_on_hand;
    stockForm.reorder_point = row.reorder_point;
}

function cancelEdit() {
    editingId.value = null;
    stockForm.reset();
}

function saveStock(row) {
    stockForm.put(`/inventory/${row.id}/stock`, {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            stockForm.reset();
        },
    });
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">{{ t('nav.inventory') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ t('ui.count', { count: stock.total }) }}</p>
            </div>

            <div class="mb-4 flex flex-wrap gap-3">
                <input
                    v-model="search"
                    @keyup.enter="applyFilters"
                    :placeholder="t('ui.search')"
                    class="w-56 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400"
                />
                <select
                    v-model="warehouseId"
                    @change="applyFilters"
                    class="w-48 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400"
                >
                    <option value="">{{ t('ui.warehouse') }}</option>
                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ displayName(w) }}</option>
                </select>
                <label class="flex items-center gap-2 text-sm text-slate-400">
                    <input v-model="lowStock" type="checkbox" @change="applyFilters" class="rounded" />
                    {{ t('reports.low_stock') }}
                </label>
            </div>

            <div class="overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-4 py-3 font-medium">{{ t('ui.sku') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.product') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.warehouse') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.quantity') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.stock') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.qty_on_hand') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.reorder_point') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.unit') }}</th>
                            <th v-if="canManage" class="px-4 py-3 font-medium">{{ t('ui.edit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in stock.data"
                            :key="row.id"
                            class="border-b border-cyan-500/5 hover:bg-white/[0.02]"
                            :class="row.qty_on_hand <= row.reorder_point ? 'bg-red-500/5' : ''"
                        >
                            <td class="px-4 py-3 font-mono text-cyan-400">{{ row.product?.sku }}</td>
                            <td class="max-w-xs truncate px-4 py-3" :title="displayName(row.product)">
                                {{ displayName(row.product) }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ displayName(row.warehouse) }}</td>
                            <td class="px-4 py-3">
                                <input
                                    v-if="editingId === row.id"
                                    v-model="stockForm.qty_on_hand"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="w-24 rounded border border-cyan-500/30 bg-[#0f172a] px-2 py-1 text-sm outline-none focus:border-cyan-400"
                                />
                                <span v-else class="font-medium text-amber-400">{{ formatNumber(row.qty_on_hand) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ formatNumber(row.qty_reserved) }}</td>
                            <td class="px-4 py-3">{{ formatNumber(availableQty(row)) }}</td>
                            <td class="px-4 py-3">
                                <input
                                    v-if="editingId === row.id"
                                    v-model="stockForm.reorder_point"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="w-24 rounded border border-cyan-500/30 bg-[#0f172a] px-2 py-1 text-sm outline-none focus:border-cyan-400"
                                />
                                <span v-else class="text-slate-400">{{ formatNumber(row.reorder_point) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ row.unit }}</td>
                            <td v-if="canManage" class="px-4 py-3">
                                <template v-if="editingId === row.id">
                                    <button
                                        @click="saveStock(row)"
                                        :disabled="stockForm.processing"
                                        class="ml-2 text-xs text-green-400 hover:text-green-300"
                                    >
                                        {{ t('ui.save') }}
                                    </button>
                                    <button @click="cancelEdit" class="text-xs text-slate-400 hover:text-slate-300">
                                        {{ t('ui.cancel') }}
                                    </button>
                                </template>
                                <button
                                    v-else
                                    @click="startEdit(row)"
                                    class="text-xs text-cyan-400 hover:text-cyan-300"
                                >
                                    {{ t('ui.update_stock') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="stock.last_page > 1" class="mt-4 flex justify-center gap-2">
                <button
                    v-for="link in stock.links"
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
