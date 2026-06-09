<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    contacts: Object,
    filters: Object,
    types: Array,
    statuses: Array,
    users: Array,
});

const { t, displayName } = useLocale();
const showForm = ref(false);

const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');
const status = ref(props.filters?.status || '');

const form = useForm({
    type: 'customer',
    name_ar: '',
    name_en: '',
    company: '',
    email: '',
    phone: '',
    emirate: '',
    status: 'active',
    assigned_to: '',
});

function applyFilters() {
    router.get('/contacts', {
        search: search.value || undefined,
        type: type.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
}

function submit() {
    form.post('/contacts', {
        onSuccess: () => {
            showForm.value = false;
            form.reset();
        },
    });
}

function typeLabel(value) {
    return props.types.find((item) => item.value === value)?.label || value;
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-2xl font-bold">{{ t('crm.contacts') }}</h1>
                <button
                    @click="showForm = !showForm"
                    class="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-bold text-[#0a0f1e] hover:bg-cyan-400"
                >
                    + {{ t('crm.new_contact') }}
                </button>
            </div>

            <div v-if="showForm" class="mb-6 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-6">
                <h2 class="mb-4 font-semibold text-cyan-400">{{ t('ui.add') }} {{ t('crm.contacts') }}</h2>
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input v-model="form.name_ar" :placeholder="t('ui.name_ar') + ' *'" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    <input v-model="form.name_en" :placeholder="t('ui.name_en') + ' *'" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    <select v-model="form.type" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option v-for="item in types" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select>
                    <input v-model="form.company" :placeholder="t('ui.company')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <input v-model="form.email" type="email" :placeholder="t('ui.email')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <input v-model="form.phone" :placeholder="t('ui.phone')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <input v-model="form.emirate" :placeholder="t('ui.emirate')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <select v-model="form.assigned_to" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option value="">— {{ t('ui.assignee') }} —</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ displayName(u) }}</option>
                    </select>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="rounded-lg bg-cyan-500 px-6 py-2 text-sm font-bold text-[#0a0f1e]">{{ t('ui.save') }}</button>
                        <button type="button" @click="showForm = false" class="rounded-lg px-6 py-2 text-sm text-slate-400">{{ t('ui.cancel') }}</button>
                    </div>
                </form>
            </div>

            <div class="mb-4 flex flex-wrap gap-3">
                <input v-model="search" @keyup.enter="applyFilters" :placeholder="t('ui.search')" class="w-48 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                <select v-model="type" @change="applyFilters" class="w-36 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                    <option value="">{{ t('crm.all_types') }}</option>
                    <option v-for="item in types" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
                <select v-model="status" @change="applyFilters" class="w-36 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                    <option value="">{{ t('crm.all_statuses') }}</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>

            <div class="overflow-hidden rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                <table class="w-full text-sm">
                    <thead class="border-b border-cyan-500/10 bg-[#0f172a] text-xs text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-start">{{ t('ui.name') }}</th>
                            <th class="px-4 py-3 text-start">{{ t('ui.type') }}</th>
                            <th class="px-4 py-3 text-start">{{ t('ui.company') }}</th>
                            <th class="px-4 py-3 text-start">{{ t('ui.phone') }}</th>
                            <th class="px-4 py-3 text-start">{{ t('ui.emirate') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="contact in contacts.data"
                            :key="contact.id"
                            class="border-b border-cyan-500/5 hover:bg-white/[0.02]"
                        >
                            <td class="px-4 py-3 font-medium">{{ displayName(contact) }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-cyan-500/10 px-2 py-0.5 text-xs text-cyan-400">
                                    {{ typeLabel(contact.type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ contact.company || '—' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ contact.phone || '—' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ contact.emirate || '—' }}</td>
                            <td class="px-4 py-3">
                                <Link :href="`/contacts/${contact.id}`" class="text-xs text-cyan-400 hover:underline">
                                    {{ t('ui.view') }}
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!contacts.data?.length">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ t('crm.no_results') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="contacts.last_page > 1" class="mt-4 flex justify-center gap-2">
                <Link
                    v-for="link in contacts.links"
                    :key="link.label"
                    :href="link.url"
                    class="rounded px-3 py-1 text-xs"
                    :class="link.active ? 'bg-cyan-500 text-[#0a0f1e]' : 'text-slate-400 hover:bg-white/5'"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>
