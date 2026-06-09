<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ logs: Object, filters: Object });

const { t, displayName, intlLocale } = useLocale();
const search = ref(props.filters?.search || '');

function applyFilters() {
    router.get('/audit-logs', { search: search.value || undefined }, { preserveState: true });
}

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString(intlLocale.value);
}

function causerName(log) {
    if (!log.causer) return '—';
    return displayName(log.causer);
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <h1 class="mb-6 text-2xl font-bold">{{ t('ui.audit_log') }}</h1>

            <div class="mb-4">
                <input
                    v-model="search"
                    @keyup.enter="applyFilters"
                    :placeholder="t('ui.search')"
                    class="w-64 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400"
                />
            </div>

            <div class="overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-4 py-3">{{ t('ui.timestamp') }}</th>
                            <th class="px-4 py-3">{{ t('ui.user') }}</th>
                            <th class="px-4 py-3">{{ t('ui.description') }}</th>
                            <th class="px-4 py-3">{{ t('ui.type') }}</th>
                            <th class="px-4 py-3">{{ t('ui.event') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id" class="border-b border-cyan-500/5 hover:bg-white/[0.02]">
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ formatDateTime(log.created_at) }}
                            </td>
                            <td class="px-4 py-3">{{ causerName(log) }}</td>
                            <td class="px-4 py-3">{{ log.description }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-cyan-400">{{ log.subject_type?.split('\\').pop() || '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ log.event || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p v-if="!logs.data?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_audit') }}</p>
        </div>
    </AppLayout>
</template>
