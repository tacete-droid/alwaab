<script setup>
import HrLayout from '@/Layouts/HrLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router } from '@inertiajs/vue3';
import { ref, nextTick, onUnmounted } from 'vue';

const props = defineProps({
    records: Object,
    filters: Object,
    todayOpen: Boolean,
    canManage: Boolean,
});

const { t, enumLabel, displayName, intlLocale } = useLocale();

const showMap = ref(false);
const selectedRecord = ref(null);
const mapContainer = ref(null);
let mapInstance = null;
let leafletLoaded = false;

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString(intlLocale.value);
}

function checkIn() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            router.post('/hr/attendance/check-in', { lat: pos.coords.latitude, lng: pos.coords.longitude });
        }, () => router.post('/hr/attendance/check-in'));
    } else {
        router.post('/hr/attendance/check-in');
    }
}

function checkOut() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            router.post('/hr/attendance/check-out', { lat: pos.coords.latitude, lng: pos.coords.longitude });
        }, () => router.post('/hr/attendance/check-out'));
    } else {
        router.post('/hr/attendance/check-out');
    }
}

function hasGps(record) {
    return record.check_in_lat != null && record.check_in_lng != null;
}

function gpsLabel(record) {
    return `${Number(record.check_in_lat).toFixed(5)}, ${Number(record.check_in_lng).toFixed(5)}`;
}

function destroyMap() {
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
    }
}

function renderMap(record) {
    destroyMap();
    if (!mapContainer.value || !window.L) return;

    const L = window.L;
    const inLat = Number(record.check_in_lat);
    const inLng = Number(record.check_in_lng);

    mapInstance = L.map(mapContainer.value).setView([inLat, inLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
    }).addTo(mapInstance);

    L.marker([inLat, inLng])
        .addTo(mapInstance)
        .bindPopup(`<b>${t('hr.check_in')}</b><br>${formatDateTime(record.check_in_at)}`)
        .openPopup();

    if (record.check_out_lat != null && record.check_out_lng != null) {
        const outLat = Number(record.check_out_lat);
        const outLng = Number(record.check_out_lng);
        L.marker([outLat, outLng], { title: t('hr.check_out') })
            .addTo(mapInstance)
            .bindPopup(`<b>${t('hr.check_out')}</b><br>${record.check_out_at ? formatDateTime(record.check_out_at) : ''}`);

        const bounds = L.latLngBounds([[inLat, inLng], [outLat, outLng]]);
        mapInstance.fitBounds(bounds, { padding: [40, 40] });
    }

    setTimeout(() => mapInstance?.invalidateSize(), 100);
}

function loadLeaflet(callback) {
    if (leafletLoaded && window.L) {
        callback();
        return;
    }

    if (!document.querySelector('link[href*="leaflet"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
    }

    const existing = document.querySelector('script[src*="leaflet"]');
    if (existing) {
        existing.addEventListener('load', () => { leafletLoaded = true; callback(); });
        if (window.L) { leafletLoaded = true; callback(); }
        return;
    }

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => { leafletLoaded = true; callback(); };
    document.head.appendChild(script);
}

async function openMap(record) {
    if (!hasGps(record)) return;
    selectedRecord.value = record;
    showMap.value = true;

    await nextTick();

    loadLeaflet(() => renderMap(record));
}

function closeMap() {
    showMap.value = false;
    selectedRecord.value = null;
    destroyMap();
}

onUnmounted(() => destroyMap());
</script>

<template>
    <HrLayout>
        <div class="mb-4 md:mb-6">
            <button
                v-if="!todayOpen"
                @click="checkIn"
                class="touch-target w-full rounded-xl bg-green-500 py-4 text-base font-bold text-[#0a0f1e] hover:bg-green-400 md:w-auto md:rounded-lg md:px-6 md:py-2.5 md:text-sm"
            >
                {{ t('hr.check_in') }}
            </button>
            <button
                v-else
                @click="checkOut"
                class="touch-target w-full rounded-xl bg-amber-500 py-4 text-base font-bold text-[#0a0f1e] hover:bg-amber-400 md:w-auto md:rounded-lg md:px-6 md:py-2.5 md:text-sm"
            >
                {{ t('hr.check_out') }}
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-green-500/15 bg-[#1a2540] md:overflow-visible">
            <table class="responsive-table w-full text-sm">
                <thead>
                    <tr class="border-b border-green-500/10 text-start text-slate-400">
                        <th v-if="canManage" class="px-4 py-3">{{ t('hr.employees') }}</th>
                        <th class="px-4 py-3">{{ t('hr.check_in') }}</th>
                        <th class="px-4 py-3">{{ t('hr.check_out') }}</th>
                        <th class="px-4 py-3">{{ t('ui.status') }}</th>
                        <th class="px-4 py-3">{{ t('ui.gps') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in records.data" :key="r.id" class="border-b border-white/5">
                        <td v-if="canManage" class="px-4 py-3" :data-label="t('hr.employees')">{{ displayName(r.user) }}</td>
                        <td class="px-4 py-3" :data-label="t('hr.check_in')">{{ formatDateTime(r.check_in_at) }}</td>
                        <td class="px-4 py-3" :data-label="t('hr.check_out')">{{ r.check_out_at ? formatDateTime(r.check_out_at) : '—' }}</td>
                        <td class="px-4 py-3" :data-label="t('ui.status')">
                            <span class="text-xs text-green-400">{{ enumLabel('hr.attendance_statuses', r.status) }}</span>
                        </td>
                        <td class="px-4 py-3" :data-label="t('ui.gps')">
                            <button
                                v-if="hasGps(r)"
                                @click="openMap(r)"
                                class="touch-target font-mono text-xs text-cyan-400 underline decoration-dotted underline-offset-2 hover:text-cyan-300"
                            >
                                {{ gpsLabel(r) }}
                            </button>
                            <span v-else class="text-slate-500">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Teleport to="body">
            <div
                v-if="showMap"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
                @click.self="closeMap"
            >
                <div class="flex w-full max-w-2xl flex-col overflow-hidden rounded-xl border border-cyan-500/20 bg-[#0f172a] shadow-2xl">
                    <div class="flex items-center justify-between border-b border-cyan-500/10 px-5 py-4">
                        <div>
                            <h3 class="font-bold text-cyan-400">{{ t('ui.gps') }}</h3>
                            <p v-if="selectedRecord" class="mt-0.5 text-xs text-slate-500">
                                {{ canManage && selectedRecord.user ? displayName(selectedRecord.user) + ' — ' : '' }}
                                {{ gpsLabel(selectedRecord) }}
                            </p>
                        </div>
                        <button
                            @click="closeMap"
                            class="rounded-lg px-3 py-1 text-sm text-slate-400 hover:bg-white/5 hover:text-white"
                        >
                            {{ t('ui.cancel') }} ✕
                        </button>
                    </div>
                    <div ref="mapContainer" class="h-80 w-full bg-[#1a2540]" />
                </div>
            </div>
        </Teleport>
    </HrLayout>
</template>
