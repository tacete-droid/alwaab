<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ settings: Object });

const { t } = useLocale();

const form = useForm({
    company_name_ar: props.settings.company_name_ar || '',
    company_name_en: props.settings.company_name_en || '',
    company_phone: props.settings.company_phone || '',
    company_email: props.settings.company_email || '',
    company_address: props.settings.company_address || '',
    company_trn: props.settings.company_trn || '',
    quotation_validity_days: Number(props.settings.quotation_validity_days) || 30,
    low_stock_alert: props.settings.low_stock_alert === '1',
});

function submit() {
    form.put('/settings');
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-2xl font-bold text-slate-200">{{ t('settings.title') }}</h1>
                <div class="flex flex-wrap gap-3 text-sm">
                    <Link href="/users" class="text-cyan-400 hover:text-cyan-300">{{ t('settings.users') }}</Link>
                    <Link href="/directives" class="text-amber-400 hover:text-amber-300">{{ t('notifications.directives') }}</Link>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-6">
                <h2 class="font-semibold text-cyan-400">{{ t('settings.company') }}</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('settings.company_name_ar') }}</label>
                        <input v-model="form.company_name_ar" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('settings.company_name_en') }}</label>
                        <input v-model="form.company_name_en" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('settings.company_phone') }}</label>
                        <input v-model="form.company_phone" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('settings.company_email') }}</label>
                        <input v-model="form.company_email" type="email" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs text-slate-500">{{ t('settings.company_address') }}</label>
                        <input v-model="form.company_address" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('settings.company_trn') }}</label>
                        <input v-model="form.company_trn" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('settings.quotation_validity_days') }}</label>
                        <input v-model="form.quotation_validity_days" type="number" min="1" max="365" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-400">
                    <input v-model="form.low_stock_alert" type="checkbox" class="rounded" />
                    {{ t('settings.low_stock_alert') }}
                </label>

                <button type="submit" :disabled="form.processing" class="rounded-lg bg-cyan-500 px-6 py-2 text-sm font-bold text-[#0a0f1e] hover:bg-cyan-400">
                    {{ t('ui.save') }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
