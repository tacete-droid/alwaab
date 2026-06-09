<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pipeline: Object,
    stages: Array,
    contacts: Array,
    users: Array,
    sources: Array,
});

const { t, displayName, formatMoney } = useLocale();
const showForm = ref(false);

const stageColors = {
    new: 'border-cyan-500/30',
    qualified: 'border-blue-500/30',
    proposal: 'border-amber-500/30',
    won: 'border-green-500/30',
    lost: 'border-red-500/30',
};

const form = useForm({
    contact_id: '',
    source: 'other',
    status: 'new',
    value_aed: 0,
    probability: 10,
    assigned_to: '',
});

function submit() {
    form.post('/leads', { onSuccess: () => { showForm.value = false; form.reset(); } });
}

function moveLead(lead, newStatus) {
    router.post(`/leads/${lead.id}/stage`, { status: newStatus }, { preserveScroll: true });
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-[1400px]">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ t('crm.pipeline') }}</h1>
                <button
                    @click="showForm = !showForm"
                    class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-[#0a0f1e] hover:bg-amber-400"
                >
                    + {{ t('crm.new_lead') }}
                </button>
            </div>

            <div v-if="showForm" class="mb-6 rounded-xl border border-amber-500/15 bg-[#1a2540] p-6">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <select v-model="form.contact_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required>
                        <option value="">{{ t('ui.select_contact') }} *</option>
                        <option v-for="c in contacts" :key="c.id" :value="c.id">
                            {{ displayName(c) }} {{ c.company ? `(${c.company})` : '' }}
                        </option>
                    </select>
                    <select v-model="form.source" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option v-for="s in sources" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                    <input v-model="form.value_aed" type="number" :placeholder="t('crm.value') + ' (AED)'" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <input v-model="form.probability" type="number" min="0" max="100" :placeholder="t('ui.probability_pct')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <select v-model="form.assigned_to" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option value="">{{ t('ui.assignee') }}</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ displayName(u) }}</option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-amber-500 px-6 py-2 text-sm font-bold text-[#0a0f1e]">{{ t('ui.save') }}</button>
                        <button type="button" @click="showForm = false" class="text-sm text-slate-400">{{ t('ui.cancel') }}</button>
                    </div>
                </form>
            </div>

            <div class="flex gap-4 overflow-x-auto pb-4">
                <div
                    v-for="stage in stages"
                    :key="stage.value"
                    class="min-w-[240px] flex-1 rounded-xl border bg-[#1a2540] p-4"
                    :class="stageColors[stage.value] || 'border-cyan-500/15'"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold">{{ stage.label }}</h3>
                        <span class="rounded-full bg-white/5 px-2 py-0.5 text-xs text-slate-400">
                            {{ pipeline[stage.value]?.length || 0 }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="lead in pipeline[stage.value]"
                            :key="lead.id"
                            class="rounded-lg border border-white/5 bg-[#0f172a] p-3"
                        >
                            <p class="mb-1 font-medium text-sm">{{ displayName(lead.contact) }}</p>
                            <p class="mb-2 text-xs text-slate-500">{{ lead.contact?.company }}</p>
                            <p class="mb-2 text-sm font-bold text-cyan-400">
                                {{ formatMoney(lead.value_aed) }} AED
                            </p>
                            <div class="flex flex-wrap gap-1">
                                <button
                                    v-for="s in stages.filter((x) => x.value !== stage.value)"
                                    :key="s.value"
                                    @click="moveLead(lead, s.value)"
                                    class="rounded bg-white/5 px-2 py-0.5 text-[10px] text-slate-400 hover:bg-cyan-500/10 hover:text-cyan-400"
                                >
                                    → {{ s.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
