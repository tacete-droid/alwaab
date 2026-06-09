<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rfqs: Object,
    filters: Object,
    statuses: Array,
    contacts: Array,
    projects: Array,
    users: Array,
});

const { t, displayName, formatDate } = useLocale();
const showForm = ref(false);
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const form = useForm({
    contact_id: '',
    project_id: '',
    description: '',
    assigned_to: '',
});

function statusLabel(value) {
    return props.statuses.find((s) => s.value === value)?.label || value;
}

function applyFilters() {
    router.get('/rfqs', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
}

function submit() {
    form.post('/rfqs', { onSuccess: () => { showForm.value = false; form.reset(); } });
}

const statusColors = {
    pending: 'text-amber-400',
    processing: 'text-cyan-400',
    quoted: 'text-green-400',
    closed: 'text-slate-500',
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ t('quotations.rfqs') }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ t('ui.count_requests', { count: rfqs.total }) }}</p>
                </div>
                <button
                    @click="showForm = !showForm"
                    class="rounded-lg bg-purple-500 px-4 py-2 text-sm font-bold text-white hover:bg-purple-400"
                >
                    + {{ t('quotations.new_rfq') }}
                </button>
            </div>

            <div v-if="showForm" class="mb-6 rounded-xl border border-purple-500/15 bg-[#1a2540] p-6">
                <h2 class="mb-4 font-semibold text-purple-400">{{ t('portal.submit_rfq') }}</h2>
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <select v-model="form.contact_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required>
                        <option value="">{{ t('ui.customer') }} *</option>
                        <option v-for="c in contacts" :key="c.id" :value="c.id">{{ displayName(c) }} — {{ c.company }}</option>
                    </select>
                    <select v-model="form.project_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option value="">{{ t('ui.project') }} ({{ t('ui.optional') }})</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ displayName(p) }}</option>
                    </select>
                    <select v-model="form.assigned_to" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option value="">{{ t('ui.assignee') }}</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ displayName(u) }}</option>
                    </select>
                    <textarea
                        v-model="form.description"
                        :placeholder="t('ui.description') + '...'"
                        rows="2"
                        class="md:col-span-2 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400"
                    />
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="rounded-lg bg-purple-500 px-6 py-2 text-sm font-bold text-white">{{ t('ui.save') }}</button>
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
                            <th class="px-4 py-3 font-medium">{{ t('ui.status') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.assignee') }}</th>
                            <th class="px-4 py-3 font-medium">{{ t('ui.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="rfq in rfqs.data" :key="rfq.id" class="border-b border-cyan-500/5 hover:bg-white/[0.02]">
                            <td class="px-4 py-3 font-mono">
                                <Link :href="`/rfqs/${rfq.id}`" class="text-purple-400 hover:underline">{{ rfq.number }}</Link>
                                <span v-if="rfq.source === 'website'" class="ms-2 rounded bg-orange-500/20 px-1.5 py-0.5 text-[10px] text-orange-300">🌐</span>
                            </td>
                            <td class="px-4 py-3">{{ displayName(rfq.contact) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ rfq.project ? displayName(rfq.project) : '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs" :class="statusColors[rfq.status]">{{ statusLabel(rfq.status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ rfq.assignee ? displayName(rfq.assignee) : '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDate(rfq.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p v-if="!rfqs.data?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_rfqs') }}</p>
        </div>
    </AppLayout>
</template>
