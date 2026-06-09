<script setup>
import HrLayout from '@/Layouts/HrLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    employees: Object,
    filters: Object,
    departments: Array,
    canManage: Boolean,
});

const { t, displayName, formatDate } = useLocale();
const search = ref(props.filters?.search || '');
const department = ref(props.filters?.department || '');

function applyFilters() {
    router.get('/hr/employees', {
        search: search.value || undefined,
        department: department.value || undefined,
    }, { preserveState: true });
}
</script>

<template>
    <HrLayout>
        <div class="mb-4 flex flex-wrap gap-3">
            <input v-model="search" @keyup.enter="applyFilters" :placeholder="t('ui.search')" class="w-48 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none focus:border-cyan-400" />
            <select v-model="department" @change="applyFilters" class="w-40 rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none">
                <option value="">{{ t('ui.all') }}</option>
                <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="emp in employees.data"
                :key="emp.id"
                :href="`/hr/employees/${emp.id}`"
                class="block rounded-xl border border-green-500/15 bg-[#1a2540] p-5 transition hover:border-green-500/30"
            >
                <div class="mb-2 flex items-start justify-between">
                    <div>
                        <h3 class="font-bold">{{ displayName(emp.user) }}</h3>
                        <p class="font-mono text-xs text-green-400">{{ emp.employee_code }}</p>
                    </div>
                    <span class="text-xs" :class="emp.user?.is_active ? 'text-green-400' : 'text-red-400'">
                        {{ emp.user?.is_active ? t('ui.active') : t('ui.inactive') }}
                    </span>
                </div>
                <p class="text-sm text-slate-400">{{ emp.job_title || '—' }}</p>
                <p class="text-xs text-slate-500">{{ emp.department }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ emp.user?.email }}</p>
                <p v-if="emp.hire_date" class="text-xs text-slate-600">{{ formatDate(emp.hire_date) }}</p>
                <p v-if="emp.emirates_id_expiry" class="mt-2 text-xs text-amber-400/80">{{ t('hr.emirates_id_expiry') }}: {{ formatDate(emp.emirates_id_expiry) }}</p>
                <p class="mt-2 text-xs text-cyan-400">{{ t('crm.view_details') }} →</p>
            </Link>
        </div>

        <p v-if="!employees.data?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_employees') }}</p>
    </HrLayout>
</template>
