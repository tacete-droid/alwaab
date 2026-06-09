<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link } from '@inertiajs/vue3';

defineProps({ project: Object });

const { t, enumLabel, displayName, formatMoney, formatDate } = useLocale();
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl">
            <Link href="/projects" class="mb-4 inline-block text-xs text-slate-500 hover:text-cyan-400">← {{ t('crm.projects') }}</Link>

            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ displayName(project) }}</h1>
                    <p class="text-sm text-slate-500">{{ project.location }}</p>
                </div>
                <span class="rounded-full bg-teal-500/20 px-3 py-1 text-sm text-teal-400">{{ project.status }}</span>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('crm.value') }}</p>
                    <p class="text-xl font-bold text-cyan-400">{{ formatMoney(project.value_aed) }} {{ t('ui.currency_aed') }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.consultant') }}</p>
                    <p>{{ project.consultant ? displayName(project.consultant) : '—' }}</p>
                </div>
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <p class="text-xs text-slate-500">{{ t('ui.contractor') }}</p>
                    <p>{{ project.contractor ? displayName(project.contractor) : '—' }}</p>
                </div>
            </div>

            <div v-if="project.lat && project.lng" class="mb-6 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                <p class="mb-2 text-sm font-medium text-cyan-400">{{ t('ui.location') }}</p>
                <a
                    :href="`https://www.google.com/maps?q=${project.lat},${project.lng}`"
                    target="_blank"
                    class="text-sm text-cyan-400 hover:underline"
                >
                    Google Maps ({{ project.lat }}, {{ project.lng }})
                </a>
            </div>

            <div v-if="project.field_visits?.length" class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                <h3 class="mb-4 font-semibold text-cyan-400">{{ t('quotations.field_visits') }}</h3>
                <div class="space-y-3">
                    <div v-for="v in project.field_visits" :key="v.id" class="rounded-lg bg-[#0f172a] p-3 text-sm">
                        <div class="flex justify-between">
                            <span>{{ v.employee ? displayName(v.employee) : '—' }}</span>
                            <span class="text-xs text-slate-500">{{ enumLabel('quotations.visit_statuses', v.status) }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ v.visited_at ? formatDate(v.visited_at) : '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
