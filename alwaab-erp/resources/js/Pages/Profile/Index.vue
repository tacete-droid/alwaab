<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ profile: Object, roles: Array });
const page = usePage();
const user = computed(() => page.props.auth.user);

const { t } = useLocale();

const form = useForm({
    name_ar: user.value?.name_ar || '',
    name_en: user.value?.name_en || '',
    phone: user.value?.phone || '',
    locale: user.value?.locale || 'ar',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.put('/profile', { preserveScroll: true });
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-6 text-2xl font-bold">{{ t('profile.title') }}</h1>

            <div v-if="profile" class="mb-6 rounded-xl border border-green-500/15 bg-[#1a2540] p-5">
                <h2 class="mb-3 font-semibold text-green-400">{{ t('profile.employee_info') }}</h2>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-slate-500">{{ t('ui.sku') }}:</span> {{ profile.employee_code }}</div>
                    <div><span class="text-slate-500">{{ t('ui.category') }}:</span> {{ profile.department }}</div>
                    <div><span class="text-slate-500">{{ t('ui.title') }}:</span> {{ profile.job_title }}</div>
                    <div>
                        <Link href="/hr/attendance" class="text-cyan-400 hover:underline">{{ t('hr.attendance') }} ←</Link>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input v-model="form.name_ar" :placeholder="t('ui.name_ar')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    <input v-model="form.name_en" :placeholder="t('ui.name_en')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                    <input v-model="form.phone" :placeholder="t('ui.phone')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    <select v-model="form.locale" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option value="ar">{{ t('profile.locale_ar') }}</option>
                        <option value="en">{{ t('profile.locale_en') }}</option>
                    </select>
                </div>

                <p class="text-xs text-slate-500">{{ t('profile.roles') }}: {{ roles?.join(', ') }}</p>
                <p class="text-xs text-slate-500">{{ t('ui.email') }}: {{ user?.email }}</p>

                <div class="border-t border-white/5 pt-4">
                    <p class="mb-3 text-sm text-slate-400">{{ t('profile.change_password') }} ({{ t('ui.optional') }})</p>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <input v-model="form.password" type="password" :placeholder="t('ui.password')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                        <input v-model="form.password_confirmation" type="password" :placeholder="t('ui.password_confirm')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                    </div>
                </div>

                <button type="submit" :disabled="form.processing" class="rounded-lg bg-cyan-500 px-6 py-2 text-sm font-bold text-[#0a0f1e]">
                    {{ t('ui.save') }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
