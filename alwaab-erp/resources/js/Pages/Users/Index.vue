<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ users: Object, filters: Object, roles: Array });

const { t, displayName, intlLocale } = useLocale();
const search = ref(props.filters?.search || '');

function applyFilters() {
    router.get('/users', { search: search.value || undefined }, { preserveState: true });
}

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString(intlLocale.value);
}

function toggleActive(user) {
    if (confirm(user.is_active ? `${t('settings.deactivate')}?` : `${t('settings.activate')}?`)) {
        router.post(`/users/${user.id}/toggle-active`);
    }
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-2xl font-bold">{{ t('settings.users') }}</h1>
                <Link href="/settings" class="text-sm text-cyan-400">← {{ t('settings.title') }}</Link>
            </div>

            <div class="mb-4">
                <input v-model="search" @keyup.enter="applyFilters" :placeholder="t('ui.search')" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none focus:border-cyan-400 sm:w-64" />
            </div>

            <div class="overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540] md:overflow-visible">
                <table class="responsive-table w-full text-sm">
                    <thead>
                        <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                            <th class="px-4 py-3">{{ t('ui.name') }}</th>
                            <th class="px-4 py-3">{{ t('ui.email') }}</th>
                            <th class="px-4 py-3">{{ t('ui.role') }}</th>
                            <th class="px-4 py-3">{{ t('ui.status') }}</th>
                            <th class="px-4 py-3">{{ t('ui.last_login') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users.data" :key="u.id" class="border-b border-white/5">
                            <td class="px-4 py-3 font-medium" :data-label="t('ui.name')">{{ displayName(u) }}</td>
                            <td class="px-4 py-3 text-slate-400" :data-label="t('ui.email')">{{ u.email }}</td>
                            <td class="px-4 py-3" :data-label="t('ui.role')">
                                <span v-for="r in u.roles" :key="r.id" class="ml-1 rounded bg-cyan-500/10 px-2 py-0.5 text-xs text-cyan-300">
                                    {{ r.name }}
                                </span>
                            </td>
                            <td class="px-4 py-3" :data-label="t('ui.status')">
                                <span :class="u.is_active ? 'text-green-400' : 'text-red-400'">
                                    {{ u.is_active ? t('ui.active') : t('ui.inactive') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500" :data-label="t('ui.last_login')">
                                {{ formatDateTime(u.last_login_at) }}
                            </td>
                            <td class="px-4 py-3" data-label="">
                                <button @click="toggleActive(u)" class="touch-target text-xs text-amber-400 hover:text-amber-300">
                                    {{ u.is_active ? t('settings.deactivate') : t('settings.activate') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
