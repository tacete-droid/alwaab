<script setup>
import HrLayout from '@/Layouts/HrLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    leaves: Object,
    filters: Object,
    types: Array,
    statuses: Array,
    canManage: Boolean,
});

const { t, displayName, formatDate } = useLocale();
const showForm = ref(false);
const status = ref(props.filters?.status || '');

const form = useForm({
    type: 'annual',
    start_date: '',
    end_date: '',
    reason: '',
});

function typeLabel(v) {
    return props.types.find((item) => item.value === v)?.label || v;
}

function statusLabel(v) {
    return props.statuses.find((s) => s.value === v)?.label || v;
}

function applyFilters() {
    router.get('/hr/leaves', { status: status.value || undefined }, { preserveState: true });
}

function submit() {
    form.post('/hr/leaves', { onSuccess: () => { showForm.value = false; form.reset(); } });
}

function approve(leave) {
    if (confirm(t('ui.confirm_approve'))) router.post(`/hr/leaves/${leave.id}/approve`);
}

function reject(leave) {
    const reason = prompt(t('ui.reason') + ' (' + t('ui.optional') + ')');
    router.post(`/hr/leaves/${leave.id}/reject`, { reason });
}

const statusColors = {
    pending: 'text-amber-400',
    approved: 'text-green-400',
    rejected: 'text-red-400',
    cancelled: 'text-slate-500',
};
</script>

<template>
    <HrLayout>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <select v-model="status" @change="applyFilters" class="w-40 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none">
                <option value="">{{ t('crm.all_statuses') }}</option>
                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
            <button @click="showForm = !showForm" class="rounded-lg bg-green-500 px-4 py-2 text-sm font-bold text-[#0a0f1e]">
                + {{ t('hr.new_leave') }}
            </button>
        </div>

        <div v-if="showForm" class="mb-6 rounded-xl border border-green-500/15 bg-[#1a2540] p-5">
            <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <select v-model="form.type" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none">
                    <option v-for="item in types" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
                <input v-model="form.start_date" type="date" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" required />
                <input v-model="form.end_date" type="date" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" required />
                <textarea v-model="form.reason" :placeholder="t('ui.reason') + '...'" rows="2" class="md:col-span-2 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" />
                <button type="submit" class="rounded-lg bg-green-500 px-6 py-2 text-sm font-bold text-[#0a0f1e]">{{ t('ui.send') }}</button>
            </form>
        </div>

        <div class="space-y-3">
            <div v-for="leave in leaves.data" :key="leave.id" class="rounded-xl border border-green-500/15 bg-[#1a2540] p-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p v-if="canManage" class="font-medium">{{ displayName(leave.user) }}</p>
                        <p class="text-sm text-slate-400">
                            {{ typeLabel(leave.type) }} — {{ leave.days }} {{ t('ui.days') }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ formatDate(leave.start_date) }}
                            →
                            {{ formatDate(leave.end_date) }}
                        </p>
                    </div>
                    <span class="text-xs" :class="statusColors[leave.status]">{{ statusLabel(leave.status) }}</span>
                </div>
                <p v-if="leave.reason" class="mt-2 text-sm text-slate-500">{{ leave.reason }}</p>
                <div v-if="canManage && leave.status === 'pending'" class="mt-3 flex gap-2">
                    <button @click="approve(leave)" class="text-xs text-green-400">{{ t('ui.approve') }}</button>
                    <button @click="reject(leave)" class="text-xs text-red-400">{{ t('ui.reject') }}</button>
                </div>
            </div>
        </div>

        <p v-if="!leaves.data?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_leaves') }}</p>
    </HrLayout>
</template>
