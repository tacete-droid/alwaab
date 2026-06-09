<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ directives: Object, roles: Array, users: Array });

const { t, displayName, intlLocale } = useLocale();
const showForm = ref(false);

const form = useForm({
    title_ar: '',
    title_en: '',
    body_ar: '',
    body_en: '',
    priority: 'normal',
    target: 'all',
    target_role: '',
    target_user_id: '',
});

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString(intlLocale.value);
}

function submit() {
    form.post('/directives', { onSuccess: () => { showForm.value = false; form.reset(); } });
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold md:text-2xl">{{ t('notifications.directives') }}</h1>
                    <p class="text-sm text-slate-500">{{ t('notifications.send_directive') }}</p>
                </div>
                <Link href="/settings" class="text-sm text-cyan-400">← {{ t('settings.title') }}</Link>
            </div>

            <button
                @click="showForm = !showForm"
                class="touch-target mb-4 w-full rounded-xl bg-amber-500 py-3 text-sm font-bold text-[#0a0f1e] md:w-auto md:px-6"
            >
                + {{ t('notifications.send_directive') }}
            </button>

            <form v-if="showForm" @submit.prevent="submit" class="mb-6 space-y-4 rounded-xl border border-amber-500/15 bg-[#1a2540] p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input v-model="form.title_ar" :placeholder="t('ui.title') + ' (AR) *'" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none" required />
                    <input v-model="form.title_en" :placeholder="t('ui.title_en') + ' *'" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none" required />
                </div>
                <textarea v-model="form.body_ar" rows="3" :placeholder="t('ui.body') + ' (AR) *'" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none" required />
                <textarea v-model="form.body_en" rows="3" :placeholder="t('ui.body_en') + ' *'" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none" required />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <select v-model="form.priority" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-3 text-sm outline-none">
                        <option value="normal">{{ t('notifications.priority_normal') }}</option>
                        <option value="urgent">{{ t('notifications.priority_urgent') }} 🚨</option>
                    </select>
                    <select v-model="form.target" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-3 text-sm outline-none">
                        <option value="all">{{ t('notifications.target_all') }}</option>
                        <option value="role">{{ t('notifications.target_role') }}</option>
                        <option value="user">{{ t('notifications.target_user') }}</option>
                    </select>
                    <select v-if="form.target === 'role'" v-model="form.target_role" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-3 text-sm outline-none" required>
                        <option value="">{{ t('ui.select') }} {{ t('ui.role') }}</option>
                        <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                    </select>
                    <select v-if="form.target === 'user'" v-model="form.target_user_id" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-3 text-sm outline-none" required>
                        <option value="">{{ t('ui.select_user') }}</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ displayName(u) }}</option>
                    </select>
                </div>

                <button type="submit" :disabled="form.processing" class="touch-target w-full rounded-lg bg-amber-500 py-3 text-sm font-bold text-[#0a0f1e] md:w-auto md:px-8">
                    {{ t('notifications.send_directive') }}
                </button>
            </form>

            <div class="space-y-3">
                <div v-for="d in directives.data" :key="d.id" class="rounded-xl border border-amber-500/10 bg-[#1a2540] p-4">
                    <div class="mb-1 flex items-center justify-between">
                        <h3 class="font-medium">{{ d.title_ar }}</h3>
                        <span :class="d.priority === 'urgent' ? 'text-red-400' : 'text-slate-500'" class="text-xs">
                            {{ d.priority === 'urgent' ? t('notifications.priority_urgent') : t('notifications.priority_normal') }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-400">{{ d.body_ar }}</p>
                    <p class="mt-2 text-[10px] text-slate-600">
                        {{ formatDateTime(d.created_at) }} —
                        {{ d.target === 'all' ? t('notifications.target_all') : d.target === 'role' ? d.target_role : t('notifications.target_user') }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
