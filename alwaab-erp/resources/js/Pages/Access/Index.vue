<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
    permissionGroups: Array,
    canCreate: Boolean,
    canUpdate: Boolean,
    canManageRoles: Boolean,
});

const { t, displayName, formatDate, intlLocale } = useLocale();
const tab = ref(props.filters?.tab || 'users');
const search = ref(props.filters?.search || '');
const showForm = ref(false);
const editingUser = ref(null);

const allPermissionNames = computed(() =>
    props.permissionGroups.flatMap((g) => g.permissions.map((p) => p.name))
);

const form = useForm({
    name_ar: '',
    name_en: '',
    email: '',
    phone: '',
    password: '',
    locale: 'ar',
    role: 'sales_rep',
    permissions: [],
    employee_code: '',
    job_title: '',
    department: '',
    is_active: true,
    salary_aed: '',
    hire_date: '',
    emergency_contact: '',
});

const roleForm = useForm({
    role: props.roles[0]?.name || '',
    permissions: [...(props.roles[0]?.permissions || [])],
});

function roleLabel(name) {
    const r = props.roles.find((x) => x.name === name);
    return r?.label || name;
}

function applyFilters() {
    router.get('/access', { search: search.value || undefined, tab: tab.value }, { preserveState: true });
}

function openCreate() {
    editingUser.value = null;
    form.reset();
    form.role = 'sales_rep';
    form.locale = 'ar';
    form.permissions = [];
    showForm.value = true;
}

function openEdit(user) {
    editingUser.value = user;
    form.name_ar = user.name_ar;
    form.name_en = user.name_en;
    form.email = user.email;
    form.phone = user.phone || '';
    form.password = '';
    form.locale = user.locale || 'ar';
    form.role = user.roles?.[0]?.name || 'sales_rep';
    form.permissions = user.permissions?.map((p) => p.name) || [];
    form.employee_code = user.employee_profile?.employee_code || '';
    form.job_title = user.employee_profile?.job_title || '';
    form.department = user.employee_profile?.department || '';
    form.is_active = user.is_active;
    form.salary_aed = user.employee_profile?.salary_aed || '';
    form.hire_date = user.employee_profile?.hire_date?.substring(0, 10) || '';
    form.emergency_contact = user.employee_profile?.emergency_contact || '';
    showForm.value = true;
}

function submitUser() {
    if (editingUser.value) {
        form.put(`/access/users/${editingUser.value.id}`, {
            onSuccess: () => { showForm.value = false; form.reset(); },
        });
    } else {
        form.post('/access/users', {
            onSuccess: () => { showForm.value = false; form.reset(); },
        });
    }
}

function togglePermission(perm) {
    const idx = form.permissions.indexOf(perm);
    if (idx >= 0) form.permissions.splice(idx, 1);
    else form.permissions.push(perm);
}

function onRoleSelect() {
    const r = props.roles.find((x) => x.name === roleForm.role);
    roleForm.permissions = [...(r?.permissions || [])];
}

function toggleRolePermission(perm) {
    const idx = roleForm.permissions.indexOf(perm);
    if (idx >= 0) roleForm.permissions.splice(idx, 1);
    else roleForm.permissions.push(perm);
}

function saveRolePermissions() {
    roleForm.put(`/access/roles/${roleForm.role}/permissions`, { preserveScroll: true });
}

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString(intlLocale.value);
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6">
                <Link href="/" class="mb-2 inline-block text-xs text-slate-500 hover:text-cyan-400">← {{ t('nav.dashboard') }}</Link>
                <h1 class="text-2xl font-bold text-violet-400">{{ t('access.title') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ t('access.subtitle') }}</p>
                <p class="mt-1 text-xs text-amber-400/80">{{ t('access.login_hint') }}</p>
            </div>

            <div class="mb-6 flex flex-wrap gap-2 border-b border-cyan-500/15 pb-3">
                <button
                    @click="tab = 'users'; applyFilters()"
                    class="rounded-lg px-4 py-2 text-sm transition"
                    :class="tab === 'users' ? 'bg-violet-500/20 font-medium text-violet-300' : 'text-slate-400 hover:bg-white/5'"
                >
                    👥 {{ t('access.users_tab') }}
                </button>
                <button
                    v-if="canManageRoles"
                    @click="tab = 'roles'; applyFilters()"
                    class="rounded-lg px-4 py-2 text-sm transition"
                    :class="tab === 'roles' ? 'bg-violet-500/20 font-medium text-violet-300' : 'text-slate-400 hover:bg-white/5'"
                >
                    🔐 {{ t('access.roles_tab') }}
                </button>
            </div>

            <!-- Users tab -->
            <div v-if="tab === 'users'">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        :placeholder="t('ui.search')"
                        class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-2 text-sm outline-none focus:border-cyan-400 sm:w-64"
                    />
                    <button
                        v-if="canCreate"
                        @click="openCreate"
                        class="rounded-lg bg-violet-500 px-5 py-2 text-sm font-bold text-white hover:bg-violet-400"
                    >
                        + {{ t('access.new_user') }}
                    </button>
                </div>

                <div v-if="showForm" class="mb-6 rounded-xl border border-violet-500/20 bg-[#1a2540] p-6">
                    <h2 class="mb-4 font-semibold text-violet-300">
                        {{ editingUser ? t('access.edit_user') : t('access.new_user') }}
                    </h2>
                    <form @submit.prevent="submitUser" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <input v-model="form.name_ar" :placeholder="t('ui.name_ar')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                            <input v-model="form.name_en" :placeholder="t('ui.name_en')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                            <input v-model="form.email" type="email" :placeholder="t('ui.email')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" required />
                            <input v-model="form.phone" :placeholder="t('ui.phone')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                            <input v-model="form.password" type="password" :placeholder="editingUser ? t('ui.password') + ' (' + t('ui.optional') + ')' : t('ui.password')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" :required="!editingUser" />
                            <select v-model="form.role" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                                <option v-for="r in roles" :key="r.name" :value="r.name">{{ r.label }}</option>
                            </select>
                            <select v-model="form.locale" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                                <option value="ar">{{ t('profile.locale_ar') }}</option>
                                <option value="en">{{ t('profile.locale_en') }}</option>
                            </select>
                            <label v-if="editingUser" class="flex items-center gap-2 text-sm text-slate-400">
                                <input v-model="form.is_active" type="checkbox" class="rounded" />
                                {{ t('ui.active') }}
                            </label>
                        </div>

                        <div class="rounded-lg border border-cyan-500/10 bg-[#0f172a]/50 p-4">
                            <h3 class="mb-3 text-sm font-medium text-cyan-400">{{ t('access.employee_details') }}</h3>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                <input v-model="form.employee_code" placeholder="EMP-001" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                                <input v-model="form.job_title" :placeholder="t('profile.employee_info')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                                <input v-model="form.department" :placeholder="t('ui.category')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                                <input v-if="editingUser" v-model="form.salary_aed" type="number" step="0.01" :placeholder="t('access.salary')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                                <input v-if="editingUser" v-model="form.hire_date" type="date" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
                                <input v-if="editingUser" v-model="form.emergency_contact" :placeholder="t('access.emergency_contact')" class="rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400 md:col-span-2" />
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-2 text-sm font-medium text-violet-300">{{ t('access.direct_permissions') }}</h3>
                            <div class="space-y-3">
                                <div v-for="group in permissionGroups" :key="group.key" class="rounded-lg border border-white/5 p-3">
                                    <p class="mb-2 text-xs font-bold text-slate-400">{{ group.label }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        <label
                                            v-for="perm in group.permissions"
                                            :key="perm.name"
                                            class="flex cursor-pointer items-center gap-1.5 rounded-md border border-cyan-500/10 px-2 py-1 text-xs transition hover:bg-white/5"
                                            :class="form.permissions.includes(perm.name) ? 'border-violet-500/40 bg-violet-500/10 text-violet-300' : 'text-slate-400'"
                                        >
                                            <input type="checkbox" class="rounded" :checked="form.permissions.includes(perm.name)" @change="togglePermission(perm.name)" />
                                            {{ perm.label }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" :disabled="form.processing" class="rounded-lg bg-violet-500 px-6 py-2 text-sm font-bold text-white disabled:opacity-50">
                                {{ t('ui.save') }}
                            </button>
                            <button type="button" @click="showForm = false" class="text-sm text-slate-400">{{ t('ui.cancel') }}</button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto rounded-xl border border-cyan-500/15 bg-[#1a2540]">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-cyan-500/10 text-start text-slate-400">
                                <th class="px-4 py-3">{{ t('ui.name') }}</th>
                                <th class="px-4 py-3">{{ t('ui.email') }}</th>
                                <th class="px-4 py-3">{{ t('access.assign_role') }}</th>
                                <th class="px-4 py-3">{{ t('ui.status') }}</th>
                                <th class="px-4 py-3">{{ t('ui.last_login') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in users.data" :key="u.id" class="border-b border-white/5 hover:bg-white/[0.02]">
                                <td class="px-4 py-3 font-medium">{{ displayName(u) }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ u.email }}</td>
                                <td class="px-4 py-3">
                                    <span v-for="r in u.roles" :key="r.id" class="rounded bg-violet-500/10 px-2 py-0.5 text-xs text-violet-300">
                                        {{ roleLabel(r.name) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="u.is_active ? 'text-green-400' : 'text-red-400'">
                                        {{ u.is_active ? t('ui.active') : t('ui.inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ formatDateTime(u.last_login_at) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link :href="`/access/users/${u.id}`" class="text-xs text-cyan-400 hover:underline">{{ t('ui.view') }}</Link>
                                        <button v-if="canUpdate" @click="openEdit(u)" class="text-xs text-amber-400 hover:underline">{{ t('ui.edit') }}</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Roles tab -->
            <div v-if="tab === 'roles' && canManageRoles">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <select v-model="roleForm.role" @change="onRoleSelect" class="w-48 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400">
                        <option v-for="r in roles.filter(x => x.name !== 'super_admin')" :key="r.name" :value="r.name">{{ r.label }}</option>
                    </select>
                    <button @click="saveRolePermissions" :disabled="roleForm.processing" class="rounded-lg bg-violet-500 px-5 py-2 text-sm font-bold text-white">
                        {{ t('access.save_role') }}
                    </button>
                </div>

                <div class="space-y-4">
                    <div v-for="group in permissionGroups" :key="group.key" class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4">
                        <h3 class="mb-3 font-medium text-cyan-400">{{ group.label }}</h3>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="perm in group.permissions"
                                :key="perm.name"
                                class="flex cursor-pointer items-center gap-1.5 rounded-md border px-2.5 py-1.5 text-xs transition"
                                :class="roleForm.permissions.includes(perm.name) ? 'border-violet-500/40 bg-violet-500/15 text-violet-300' : 'border-white/10 text-slate-400 hover:bg-white/5'"
                            >
                                <input type="checkbox" class="rounded" :checked="roleForm.permissions.includes(perm.name)" @change="toggleRolePermission(perm.name)" />
                                {{ perm.label }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
