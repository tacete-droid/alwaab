<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps({ projects: Array });

const { t, displayName } = useLocale();
const gpsStatus = ref('');
const gpsReady = ref(false);

const form = useForm({
    project_id: '',
    lat: null,
    lng: null,
    notes: '',
});

function captureGps() {
    if (!navigator.geolocation) {
        gpsStatus.value = t('ui.gps') + ' —';
        return;
    }

    gpsStatus.value = t('ui.loading');
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            form.lat = pos.coords.latitude;
            form.lng = pos.coords.longitude;
            gpsReady.value = true;
            gpsStatus.value = `${form.lat.toFixed(5)}, ${form.lng.toFixed(5)}`;
        },
        () => {
            gpsStatus.value = t('ui.optional');
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
}

function submit() {
    form.post('/field-visits');
}

onMounted(captureGps);
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-lg">
            <h1 class="mb-2 text-xl font-bold md:text-2xl">{{ t('ui.new_visit') }}</h1>
            <p class="mb-6 text-sm text-slate-500">{{ t('ui.start_visit') }}</p>

            <form @submit.prevent="submit" class="space-y-5 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                <div>
                    <label class="mb-2 block text-xs text-slate-500">{{ t('ui.project') }} *</label>
                    <select
                        v-model="form.project_id"
                        class="touch-target w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none focus:border-cyan-400"
                        required
                    >
                        <option value="">{{ t('ui.select_project') }}</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">
                            {{ displayName(p) }} {{ p.location ? `— ${p.location}` : '' }}
                        </option>
                    </select>
                </div>

                <div class="rounded-lg border border-cyan-500/10 bg-[#0f172a] p-4">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs text-slate-500">{{ t('ui.gps') }}</span>
                        <button type="button" @click="captureGps" class="text-xs text-cyan-400">{{ t('ui.edit') }}</button>
                    </div>
                    <p class="font-mono text-sm" :class="gpsReady ? 'text-green-400' : 'text-slate-400'">{{ gpsStatus }}</p>
                </div>

                <div>
                    <label class="mb-2 block text-xs text-slate-500">{{ t('ui.notes') }} ({{ t('ui.optional') }})</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none focus:border-cyan-400"
                        :placeholder="t('ui.notes') + '...'"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing || !form.project_id"
                    class="touch-target w-full rounded-xl bg-cyan-500 py-4 text-base font-bold text-[#0a0f1e] hover:bg-cyan-400 disabled:opacity-50"
                >
                    {{ form.processing ? t('ui.processing') : t('ui.start_visit') }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
