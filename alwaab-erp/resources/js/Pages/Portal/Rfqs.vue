<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ contact: Object, rfqs: Object });
const { t, enumLabel } = useLocale();
const showForm = ref(false);
const form = useForm({ contact_id: props.contact?.id, project_id: '', description: '' });

function submit() {
    form.post('/portal/rfqs', { onSuccess: () => { showForm.value = false; form.reset(); } });
}
</script>

<template>
    <PortalLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-bold">{{ t('portal.my_rfqs') }}</h1>
            <button v-if="contact" @click="showForm = !showForm" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-[#0a0f1e]">
                + {{ t('portal.submit_rfq') }}
            </button>
        </div>

        <div v-if="showForm" class="mb-6 rounded-xl border border-amber-500/15 bg-[#1a2540] p-5">
            <form @submit.prevent="submit" class="space-y-3">
                <textarea v-model="form.description" rows="3" :placeholder="t('ui.description') + '...'" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-2 text-sm outline-none" required />
                <button type="submit" class="rounded-lg bg-amber-500 px-6 py-2 text-sm font-bold text-[#0a0f1e]">{{ t('ui.send') }}</button>
            </form>
        </div>

        <div v-if="rfqs.data?.length" class="space-y-3">
            <div v-for="rfq in rfqs.data" :key="rfq.id" class="rounded-xl border border-amber-500/15 bg-[#1a2540] p-4">
                <div class="flex justify-between">
                    <span class="font-mono text-amber-400">{{ rfq.number }}</span>
                    <span class="text-xs text-slate-500">{{ enumLabel('quotations.rfq_statuses', rfq.status) }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-400">{{ rfq.description }}</p>
            </div>
        </div>
        <p v-else class="text-center text-slate-500">{{ t('ui.no_rfqs') }}</p>
    </PortalLayout>
</template>
