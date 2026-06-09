<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link } from '@inertiajs/vue3';

defineProps({ contact: Object });

const { t, enumLabel, displayName, formatMoney } = useLocale();
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-4xl">
            <Link href="/contacts" class="mb-4 inline-block text-sm text-cyan-400 hover:underline">← {{ t('ui.back_to_contacts') }}</Link>

            <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-6">
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">{{ displayName(contact) }}</h1>
                        <p class="text-slate-400">{{ contact.company }}</p>
                    </div>
                    <span class="rounded-full bg-cyan-500/10 px-3 py-1 text-xs text-cyan-400">{{ enumLabel('crm.types', contact.type) }}</span>
                </div>

                <div class="mb-6 grid grid-cols-2 gap-4 text-sm md:grid-cols-3">
                    <div><span class="text-slate-500">{{ t('ui.email') }}:</span> {{ contact.email || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.phone') }}:</span> {{ contact.phone || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.emirate') }}:</span> {{ contact.emirate || '—' }}</div>
                    <div><span class="text-slate-500">{{ t('ui.status') }}:</span> {{ enumLabel('crm.statuses', contact.status) }}</div>
                    <div><span class="text-slate-500">{{ t('ui.assignee') }}:</span> {{ displayName(contact.assignee) }}</div>
                </div>

                <div v-if="contact.notes" class="mb-6 rounded-lg bg-[#0f172a] p-4 text-sm text-slate-300">
                    {{ contact.notes }}
                </div>

                <h2 class="mb-3 font-semibold text-amber-400">{{ t('crm.leads') }}</h2>
                <div v-if="contact.leads?.length" class="space-y-2">
                    <div
                        v-for="lead in contact.leads"
                        :key="lead.id"
                        class="flex justify-between rounded-lg bg-[#0f172a] px-4 py-3 text-sm"
                    >
                        <span>{{ enumLabel('crm.lead_stages', lead.status) }}</span>
                        <span class="text-cyan-400">{{ formatMoney(lead.value_aed) }} {{ t('ui.currency_aed') }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500">{{ t('ui.no_leads') }}</p>
            </div>
        </div>
    </AppLayout>
</template>
