<script setup>
import HrLayout from '@/Layouts/HrLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    employee: Object,
    documents: Array,
    documentTypes: Array,
    canManage: Boolean,
    canUpload: Boolean,
});

const { t, displayName, formatMoney, formatDate } = useLocale();
const showUpload = ref(false);

const form = useForm({
    job_title: props.employee.job_title || '',
    department: props.employee.department || '',
    hire_date: props.employee.hire_date?.substring(0, 10) || '',
    emergency_contact: props.employee.emergency_contact || '',
    emirates_id: props.employee.emirates_id || '',
    emirates_id_expiry: props.employee.emirates_id_expiry?.substring(0, 10) || '',
    passport_number: props.employee.passport_number || '',
    passport_expiry: props.employee.passport_expiry?.substring(0, 10) || '',
    residency_number: props.employee.residency_number || '',
    residency_expiry: props.employee.residency_expiry?.substring(0, 10) || '',
    basic_salary: props.employee.basic_salary || '',
    housing_allowance: props.employee.housing_allowance || '',
    salary_aed: props.employee.salary_aed || '',
    iban: props.employee.iban || '',
});

const docForm = useForm({
    file: null,
    document_type: 'emirates_id',
    title: '',
    expires_at: '',
});

const totalCompensation = computed(() =>
    Number(form.basic_salary || props.employee.basic_salary || props.employee.salary_aed || 0)
    + Number(form.housing_allowance || props.employee.housing_allowance || 0)
);

function isExpiringSoon(date) {
    if (!date) return false;
    const expiry = new Date(date);
    const now = new Date();
    const diff = (expiry - now) / (1000 * 60 * 60 * 24);
    return diff >= 0 && diff <= 30;
}

function submitProfile() {
    form.put(`/hr/employees/${props.employee.id}`, { preserveScroll: true });
}

function onFileChange(event) {
    docForm.file = event.target.files[0];
}

function submitDocument() {
    docForm.post(`/hr/employees/${props.employee.id}/documents`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            docForm.reset();
            showUpload.value = false;
        },
    });
}

function deleteDocument(id) {
    if (confirm(t('ui.confirm_delete'))) {
        router.delete(`/hr/employees/${props.employee.id}/documents/${id}`, { preserveScroll: true });
    }
}

function typeLabel(value) {
    return props.documentTypes.find((x) => x.value === value)?.label || value;
}
</script>

<template>
    <HrLayout>
        <div class="mx-auto max-w-5xl">
            <Link href="/hr/employees" class="mb-4 inline-block text-sm text-cyan-400 hover:underline">
                ← {{ t('hr.employees') }}
            </Link>

            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ displayName(employee.user) }}</h1>
                    <p class="font-mono text-sm text-green-400">{{ employee.employee_code }}</p>
                    <p class="text-sm text-slate-400">{{ employee.user?.email }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs" :class="employee.user?.is_active ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400'">
                    {{ employee.user?.is_active ? t('ui.active') : t('ui.inactive') }}
                </span>
            </div>

            <form v-if="canManage" @submit.prevent="submitProfile" class="mb-6 space-y-6">
                <section class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                    <h2 class="mb-4 font-semibold text-cyan-400">{{ t('hr.uae_identity') }}</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.emirates_id') }}</label>
                            <input v-model="form.emirates_id" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" dir="ltr" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.emirates_id_expiry') }}</label>
                            <input v-model="form.emirates_id_expiry" type="date" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" :class="isExpiringSoon(form.emirates_id_expiry) ? 'border-amber-500/50' : ''" />
                            <p v-if="isExpiringSoon(form.emirates_id_expiry)" class="mt-1 text-xs text-amber-400">{{ t('hr.expiring_soon') }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.passport_number') }}</label>
                            <input v-model="form.passport_number" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" dir="ltr" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.passport_expiry') }}</label>
                            <input v-model="form.passport_expiry" type="date" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" :class="isExpiringSoon(form.passport_expiry) ? 'border-amber-500/50' : ''" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.residency_number') }}</label>
                            <input v-model="form.residency_number" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" dir="ltr" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.residency_expiry') }}</label>
                            <input v-model="form.residency_expiry" type="date" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" :class="isExpiringSoon(form.residency_expiry) ? 'border-amber-500/50' : ''" />
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-green-500/15 bg-[#1a2540] p-5">
                    <h2 class="mb-4 font-semibold text-green-400">{{ t('hr.compensation') }}</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.basic_salary') }}</label>
                            <input v-model="form.basic_salary" type="number" step="0.01" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" dir="ltr" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.housing_allowance') }}</label>
                            <input v-model="form.housing_allowance" type="number" step="0.01" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" dir="ltr" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs text-slate-500">{{ t('hr.iban') }}</label>
                            <input v-model="form.iban" placeholder="AE..." class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 font-mono text-sm outline-none" dir="ltr" />
                        </div>
                        <div class="md:col-span-2 text-sm text-slate-400">
                            {{ t('hr.total_compensation') }}:
                            <span class="font-bold text-cyan-400">{{ formatMoney(totalCompensation) }} {{ t('ui.currency_aed') }}</span>
                        </div>
                    </div>
                </section>

                <button type="submit" :disabled="form.processing" class="rounded-lg bg-cyan-500 px-6 py-2 text-sm font-bold text-[#0a0f1e] disabled:opacity-50">
                    {{ t('ui.save') }}
                </button>
            </form>

            <div v-else class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <section class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5 text-sm">
                    <h2 class="mb-3 font-semibold text-cyan-400">{{ t('hr.uae_identity') }}</h2>
                    <div class="space-y-2">
                        <div><span class="text-slate-500">{{ t('hr.emirates_id') }}:</span> {{ employee.emirates_id || '—' }}</div>
                        <div :class="isExpiringSoon(employee.emirates_id_expiry) ? 'text-amber-400' : ''">
                            <span class="text-slate-500">{{ t('hr.emirates_id_expiry') }}:</span> {{ employee.emirates_id_expiry ? formatDate(employee.emirates_id_expiry) : '—' }}
                        </div>
                        <div><span class="text-slate-500">{{ t('hr.passport_number') }}:</span> {{ employee.passport_number || '—' }}</div>
                        <div :class="isExpiringSoon(employee.passport_expiry) ? 'text-amber-400' : ''">
                            <span class="text-slate-500">{{ t('hr.passport_expiry') }}:</span> {{ employee.passport_expiry ? formatDate(employee.passport_expiry) : '—' }}
                        </div>
                        <div><span class="text-slate-500">{{ t('hr.residency_number') }}:</span> {{ employee.residency_number || '—' }}</div>
                        <div :class="isExpiringSoon(employee.residency_expiry) ? 'text-amber-400' : ''">
                            <span class="text-slate-500">{{ t('hr.residency_expiry') }}:</span> {{ employee.residency_expiry ? formatDate(employee.residency_expiry) : '—' }}
                        </div>
                    </div>
                </section>
                <section class="rounded-xl border border-green-500/15 bg-[#1a2540] p-5 text-sm">
                    <h2 class="mb-3 font-semibold text-green-400">{{ t('hr.compensation') }}</h2>
                    <div class="space-y-2">
                        <div><span class="text-slate-500">{{ t('hr.basic_salary') }}:</span> {{ employee.basic_salary ? formatMoney(employee.basic_salary) : '—' }}</div>
                        <div><span class="text-slate-500">{{ t('hr.housing_allowance') }}:</span> {{ employee.housing_allowance ? formatMoney(employee.housing_allowance) : '—' }}</div>
                        <div><span class="text-slate-500">{{ t('hr.iban') }}:</span> <span class="font-mono">{{ employee.iban || '—' }}</span></div>
                    </div>
                </section>
            </div>

            <section class="rounded-xl border border-amber-500/15 bg-[#1a2540] p-5">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-semibold text-amber-400">{{ t('hr.documents') }}</h2>
                    <button
                        v-if="canUpload"
                        type="button"
                        @click="showUpload = !showUpload"
                        class="rounded-lg bg-amber-500/20 px-4 py-2 text-sm text-amber-400 hover:bg-amber-500/30"
                    >
                        + {{ t('hr.upload_document') }}
                    </button>
                </div>

                <form v-if="showUpload && canUpload" @submit.prevent="submitDocument" class="mb-4 grid grid-cols-1 gap-3 rounded-lg border border-amber-500/10 bg-[#0f172a] p-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs text-slate-500">{{ t('hr.upload_document') }}</label>
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-sm" @change="onFileChange" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('hr.document_type') }}</label>
                        <select v-model="docForm.document_type" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none">
                            <option v-for="dt in documentTypes" :key="dt.value" :value="dt.value">{{ dt.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('hr.expires_at') }}</label>
                        <input v-model="docForm.expires_at" type="date" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" />
                    </div>
                    <div class="md:col-span-2">
                        <input v-model="docForm.title" :placeholder="t('ui.title')" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" />
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" :disabled="docForm.processing" class="rounded-lg bg-amber-500 px-6 py-2 text-sm font-bold text-[#0a0f1e] disabled:opacity-50">
                            {{ t('ui.upload') }}
                        </button>
                    </div>
                </form>

                <div v-if="documents.length" class="space-y-2">
                    <div
                        v-for="doc in documents"
                        :key="doc.id"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-[#0f172a] px-4 py-3 text-sm"
                    >
                        <div>
                            <p class="font-medium">{{ doc.title }}</p>
                            <p class="text-xs text-slate-500">
                                {{ typeLabel(doc.document_type) }}
                                <span v-if="doc.expires_at" :class="isExpiringSoon(doc.expires_at) ? 'text-amber-400' : ''">
                                    — {{ formatDate(doc.expires_at) }}
                                </span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="doc.url" target="_blank" rel="noopener" class="text-xs text-cyan-400 hover:underline">{{ t('ui.view') }}</a>
                            <button v-if="canUpload" type="button" @click="deleteDocument(doc.id)" class="text-xs text-red-400 hover:underline">{{ t('ui.delete') }}</button>
                        </div>
                    </div>
                </div>
                <p v-else class="text-center text-sm text-slate-500">{{ t('hr.no_documents') }}</p>
            </section>
        </div>
    </HrLayout>
</template>
