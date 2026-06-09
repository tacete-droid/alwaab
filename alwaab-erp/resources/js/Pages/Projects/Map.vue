<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({ markers: Array });
const { t, formatMoney } = useLocale();
const mapEl = ref(null);
let map = null;

onMounted(() => {
    if (!mapEl.value || !props.markers?.length) return;

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => {
        const L = window.L;
        const center = props.markers[0];
        map = L.map(mapEl.value).setView([center.lat, center.lng], 9);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
        }).addTo(map);

        props.markers.forEach((m) => {
            L.marker([m.lat, m.lng])
                .addTo(map)
                .bindPopup(`<b>${m.name}</b><br>${m.location || ''}<br>${formatMoney(m.value)} AED`);
        });
    };
    document.head.appendChild(script);
});

onUnmounted(() => { map?.remove(); });
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ t('ui.map') }} — {{ t('crm.projects') }}</h1>
                <Link href="/projects" class="text-sm text-cyan-400 hover:text-cyan-300">← {{ t('ui.back') }}</Link>
            </div>
            <div ref="mapEl" class="h-[calc(100vh-200px)] min-h-[400px] rounded-xl border border-cyan-500/15 bg-[#1a2540]" />
            <p v-if="!markers?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_projects') }}</p>
        </div>
    </AppLayout>
</template>
