<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GpsMap from '@/Components/GpsMap.vue';
import { useLocale } from '@/composables/useLocale';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    markers: Array,
    canManage: Boolean,
});

const { t, enumLabel, intlLocale } = useLocale();
const liveMarkers = ref([]);
const lastUpdate = ref(null);
let pollTimer = null;

const statusColors = {
    in_progress: '#22d3ee',
    completed: '#22c55e',
    cancelled: '#ef4444',
};

const allMarkers = computed(() => {
    if (props.canManage && liveMarkers.value.length) {
        return liveMarkers.value.map((m) => ({
            lat: m.lat,
            lng: m.lng,
            color: statusColors[m.status] || '#22d3ee',
            popup: `<b>${m.project || '—'}</b><br>${m.employee || ''}<br><span style="color:#22d3ee">● ${t('ui.in_progress')}</span><br><a href="/field-visits/${m.id}">${t('ui.view')}</a>`,
        }));
    }

    return (props.markers || []).map((m) => ({
        lat: m.lat,
        lng: m.lng,
        color: statusColors[m.status] || '#22c55e',
        popup: `<b>${m.project || '—'}</b><br>${m.employee || ''}<br>${enumLabel('quotations.visit_statuses', m.status)}<br><a href="/field-visits/${m.id}">${t('ui.view')}</a>`,
    }));
});

async function fetchLiveGps() {
    if (!props.canManage) return;
    try {
        const res = await fetch('/field-visits/live-gps', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const data = await res.json();
        liveMarkers.value = data.visits || [];
        lastUpdate.value = new Date();
    } catch {
        // silent
    }
}

onMounted(() => {
    if (props.canManage) {
        fetchLiveGps();
        pollTimer = setInterval(fetchLiveGps, 15000);
    }
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold md:text-2xl">{{ t('ui.start_visit_map') }}</h1>
                    <p v-if="canManage" class="mt-1 text-xs text-green-400">
                        ● {{ t('ui.gps') }} {{ t('ui.live') }}
                        <span v-if="lastUpdate" class="text-slate-500"> — {{ lastUpdate.toLocaleTimeString(intlLocale) }}</span>
                    </p>
                </div>
                <Link href="/field-visits" class="text-sm text-cyan-400 hover:text-cyan-300">← {{ t('ui.back') }}</Link>
            </div>

            <div v-if="canManage" class="mb-3 flex flex-wrap gap-3 text-xs text-slate-500">
                <span><span class="inline-block h-2 w-2 rounded-full bg-cyan-400"></span> {{ t('ui.in_progress') }}</span>
                <span><span class="inline-block h-2 w-2 rounded-full bg-green-400"></span> {{ t('ui.completed') }}</span>
                <span class="text-amber-400">{{ liveMarkers.length }}</span>
            </div>

            <GpsMap v-if="allMarkers.length" :markers="allMarkers" height="calc(100dvh - 220px)" />
            <p v-else class="py-12 text-center text-slate-500">{{ t('ui.no_visits') }}</p>
        </div>
    </AppLayout>
</template>
