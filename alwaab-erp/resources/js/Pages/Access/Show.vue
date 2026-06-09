<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    accessUser: Object,
    permissionGroups: Array,
    canUpdate: Boolean,
});

const { t, displayName, formatDate, intlLocale } = useLocale();

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString(intlLocale.value);
}

function hasPermission(name) {
    return props.accessUser.permissions?.includes(name)
        || props.accessUser.role_permissions?.includes(name);
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-4xl">
            <Link href="/access" class="mb-4 inline-block text-xs text-slate-500 hover:text-cyan-400">← {{ t('access.title') }}</Link>

            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-violet-400">{{ displayName(accessUser) }}</h1>
                    <p class="text-sm text-slate-500">{{ accessUser.email }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs" :class="accessUser.is_active ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'">
                    {{ accessUser.is_active ? t('ui.active') : t('ui.inactive') }}
                </span>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                    <h2 class="mb-3 text-sm font-semibold text-cyan-400">{{ t('access.user_details') }}</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('ui.phone') }}</dt><dd>{{ accessUser.phone || '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('access.assign_role') }}</dt><dd>{{ accessUser.roles?.join(', ') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('ui.last_login') }}</dt><dd>{{ formatDateTime(accessUser.last_login_at) }}</dd></div>
                    </dl>
                </div>

                <div v-if="accessUser.employee_profile" class="rounded-xl border border-amber-500/15 bg-[#1a2540] p-4">
                    <h2 class="mb-3 text-sm font-semibold text-amber-400">{{ t('access.employee_details') }}</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('ui.sku') }}</dt><dd class="font-mono">{{ accessUser.employee_profile.employee_code }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('ui.title') }}</dt><dd>{{ accessUser.employee_profile.job_title || '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('ui.category') }}</dt><dd>{{ accessUser.employee_profile.department || '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('access.salary') }}</dt><dd>{{ accessUser.employee_profile.salary_aed ? Number(accessUser.employee_profile.salary_aed).toLocaleString(intlLocale) + ' AED' : '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('access.hire_date') }}</dt><dd>{{ formatDate(accessUser.employee_profile.hire_date) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ t('access.emergency_contact') }}</dt><dd>{{ accessUser.employee_profile.emergency_contact || '—' }}</dd></div>
                    </dl>
                </div>
            </div>

            <div class="rounded-xl border border-violet-500/15 bg-[#1a2540] p-5">
                <h2 class="mb-4 font-semibold text-violet-300">{{ t('access.menu_permissions') }}</h2>
                <div class="space-y-3">
                    <div v-for="group in permissionGroups" :key="group.key">
                        <p class="mb-1 text-xs font-bold text-slate-500">{{ group.label }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="perm in group.permissions"
                                :key="perm.name"
                                class="rounded px-2 py-0.5 text-[10px]"
                                :class="hasPermission(perm.name) ? 'bg-green-500/15 text-green-300' : 'bg-white/5 text-slate-600 line-through'"
                            >
                                {{ perm.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
