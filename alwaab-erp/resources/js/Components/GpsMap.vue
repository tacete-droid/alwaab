<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    markers: { type: Array, default: () => [] },
    height: { type: String, default: '280px' },
    fitBounds: { type: Boolean, default: true },
});

const mapEl = ref(null);
let mapInstance = null;
let markerLayer = [];
let leafletLoaded = false;

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
        if (window.L) {
            leafletLoaded = true;
            callback();
        } else {
            existing.addEventListener('load', () => { leafletLoaded = true; callback(); });
        }
        return;
    }

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => { leafletLoaded = true; callback(); };
    document.head.appendChild(script);
}

function clearMarkers() {
    markerLayer.forEach((m) => m.remove());
    markerLayer = [];
}

function renderMarkers() {
    if (!mapInstance || !window.L) return;
    const L = window.L;
    clearMarkers();

    const points = [];

    props.markers.forEach((m) => {
        if (m.lat == null || m.lng == null) return;
        const lat = Number(m.lat);
        const lng = Number(m.lng);
        points.push([lat, lng]);

        const icon = m.color
            ? L.divIcon({
                className: '',
                html: `<div style="background:${m.color};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 0 4px rgba(0,0,0,.5)"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            })
            : undefined;

        const marker = L.marker([lat, lng], icon ? { icon } : {})
            .addTo(mapInstance);

        if (m.popup) marker.bindPopup(m.popup);
        if (m.label && !m.popup) marker.bindTooltip(m.label);

        markerLayer.push(marker);
    });

    if (props.fitBounds && points.length > 1) {
        mapInstance.fitBounds(points, { padding: [30, 30] });
    } else if (points.length === 1) {
        mapInstance.setView(points[0], 15);
    }
}

function initMap() {
    if (!mapEl.value || !window.L) return;
    const L = window.L;

    if (!mapInstance) {
        const center = props.markers.find((m) => m.lat != null) || { lat: 25.2048, lng: 55.2708 };
        mapInstance = L.map(mapEl.value).setView([Number(center.lat), Number(center.lng)], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
        }).addTo(mapInstance);
    }

    renderMarkers();
    setTimeout(() => mapInstance?.invalidateSize(), 150);
}

watch(() => props.markers, () => renderMarkers(), { deep: true });

onMounted(() => loadLeaflet(initMap));
onUnmounted(() => {
    clearMarkers();
    mapInstance?.remove();
    mapInstance = null;
});

defineExpose({ refresh: () => mapInstance?.invalidateSize() });
</script>

<template>
    <div ref="mapEl" class="w-full rounded-xl border border-cyan-500/15 bg-[#1a2540]" :style="{ height }" />
</template>
