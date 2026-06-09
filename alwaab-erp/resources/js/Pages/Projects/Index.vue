<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    projects: Object,
    filters: Object,
    statuses: Array,
    contacts: Array,
    users: Array,
});

const { t, enumLabel, displayName, formatMoney } = useLocale();
const showForm = ref(false);
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const form = useForm({
    name_ar: '',
    name_en: '',
    location: '',
    status: 'prospecting',
    value_aed: 0,
    consultant_id: '',
    contractor_id: '',
    assigned_to: '',
});

function applyFilters() {
    router.get('/projects', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
}

function submit() {
    form.post('/projects', { onSuccess: () => { showForm.value = false; form.reset(); } });
}

const statusColors = {
    prospecting: 'text-cyan-400',
    active: 'text-green-400',
    completed: 'text-slate-400',
    on_hold: 'text-amber-400',
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-2xl font-bold">{{ t('crm.projects') }}</h1>
                <a href="/projects/map" class="rounded-lg border border-teal-500/30 px-4 py-2 text-sm text-teal-400 hover:bg-teal-500/10">
                    {{ t('ui.map') }}
                </a>
                <button
                    @click="showForm = !showForm"
                    class="rounded-lg bg-teal-500 px-4 py-2 text-sm font-bold text-[#0a0f1e] hover:bg-teal-400"
                >
                    + {{ t('crm.new_project') }}
                </button>
            </div>

            <div v-if="showForm" class="mb-6 rounded-xl border border-teal-500/15 bg-[#1a2540] p-6">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input v-model="form.name_ar" :placeholder="t('ui.name_ar') + ' *'" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    <input v-model="form.name_en" :placeholder="t('ui.name_en') + ' *'" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    <input v-model="form.location" :placeholder="t('ui.location')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <input v-model="form.value_aed" type="number" :placeholder="t('ui.price_aed')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <select v-model="form.consultant_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option value="">{{ t('ui.consultant') }}</option>
                        <option v-for="c in contacts.filter(x => x.type === 'consultant')" :key="c.id" :value="c.id">{{ displayName(c) }}</option>
                    </select>
                    <select v-model="form.contractor_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option value="">{{ t('ui.contractor') }}</option>
                        <option v-for="c in contacts.filter(x => x.type === 'contractor')" :key="c.id" :value="c.id">{{ displayName(c) }}</option>
                    </select>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="rounded-lg bg-teal-500 px-6 py-2 text-sm font-bold text-[#0a0f1e]">{{ t('ui.save') }}</button>
                        <button type="button" @click="showForm = false" class="text-sm text-slate-400">{{ t('ui.cancel') }}</button>
                    </div>
                </form>
            </div>

            <div class="mb-4 flex gap-3">
                <input v-model="search" @keyup.enter="applyFilters" :placeholder="t('ui.search')" class="w-48 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                <select v-model="status" @change="applyFilters" class="w-36 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                    <option value="">{{ t('crm.all_statuses') }}</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="project in projects.data"
                    :key="project.id"
                    :href="`/projects/${project.id}`"
                    class="block rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5 transition hover:border-cyan-500/30"
                >
                    <div class="mb-2 flex items-start justify-between">
                        <h3 class="font-bold">{{ displayName(project) }}</h3>
                        <span class="text-xs" :class="statusColors[project.status]">{{ enumLabel('crm.project_statuses', project.status) }}</span>
                    </div>
                    <p class="mb-3 text-xs text-slate-500">{{ project.location || '—' }}</p>
                    <p class="text-lg font-bold text-cyan-400">
                        {{ formatMoney(project.value_aed) }}
                        <span class="text-xs font-normal text-slate-500">{{ t('ui.currency_aed') }}</span>
                    </p>
                    <div class="mt-3 border-t border-white/5 pt-3 text-xs text-slate-500">
                        <p v-if="project.consultant">{{ t('ui.consultant') }}: {{ displayName(project.consultant) }}</p>
                        <p v-if="project.contractor">{{ t('ui.contractor') }}: {{ displayName(project.contractor) }}</p>
                    </div>
                </Link>
            </div>

            <p v-if="!projects.data?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_projects') }}</p>
        </div>
    </AppLayout>
</template>
