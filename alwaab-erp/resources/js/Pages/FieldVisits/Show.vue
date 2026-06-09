<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GpsMap from '@/Components/GpsMap.vue';
import { useLocale } from '@/composables/useLocale';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    visit: Object,
    canManage: Boolean,
    isOwner: Boolean,
});

const { t, displayName, intlLocale } = useLocale();
const isActive = computed(() => props.visit?.status === 'in_progress');
const photoInput = ref(null);
const gpsCoords = ref({
    lat: props.visit?.lat ? Number(props.visit.lat) : null,
    lng: props.visit?.lng ? Number(props.visit.lng) : null,
});
const gpsTracking = ref(false);

const completeForm = useForm({ notes: props.visit?.notes || '' });

let locationTimer = null;

function formatDateTime(date) {
    if (!date) return '';
    return new Date(date).toLocaleString(intlLocale.value);
}

const mapMarkers = computed(() => {
    const markers = [];

    if (gpsCoords.value.lat && gpsCoords.value.lng) {
        markers.push({
            lat: gpsCoords.value.lat,
            lng: gpsCoords.value.lng,
            color: isActive.value ? '#22d3ee' : '#22c55e',
            popup: `<b>${props.visit.project ? displayName(props.visit.project) : t('quotations.field_visits')}</b><br>${props.visit.employee ? displayName(props.visit.employee) : ''}<br>${isActive.value ? t('ui.in_progress') : t('ui.completed')}`,
            label: t('ui.location'),
        });
    }

    props.visit?.photos?.forEach((photo, i) => {
        if (photo.lat && photo.lng) {
            markers.push({
                lat: Number(photo.lat),
                lng: Number(photo.lng),
                color: '#f59e0b',
                popup: `${t('ui.photo')} ${i + 1}`,
                label: `${t('ui.photo')} ${i + 1}`,
            });
        }
    });

    if (props.visit?.project?.lat && props.visit?.project?.lng) {
        markers.push({
            lat: Number(props.visit.project.lat),
            lng: Number(props.visit.project.lng),
            color: '#a78bfa',
            popup: `<b>${t('ui.project')}</b><br>${props.visit.project.location || ''}`,
            label: t('ui.project'),
        });
    }

    return markers;
});

function mapsUrl(lat, lng) {
    return `https://www.google.com/maps?q=${lat},${lng}`;
}

function captureGps(callback) {
    if (!navigator.geolocation) {
        callback?.();
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            gpsCoords.value = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            callback?.();
        },
        () => callback?.(),
        { enableHighAccuracy: true, timeout: 15000 }
    );
}

function pushLocationToServer() {
    if (!isActive.value || !props.isOwner || !gpsCoords.value.lat) return;

    fetch(`/field-visits/${props.visit.id}/location`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ lat: gpsCoords.value.lat, lng: gpsCoords.value.lng }),
    }).then(() => { gpsTracking.value = true; }).catch(() => {});
}

function startGpsTracking() {
    if (!isActive.value || !props.isOwner) return;
    captureGps(pushLocationToServer);
    locationTimer = setInterval(() => captureGps(pushLocationToServer), 45000);
}

function takePhoto() {
    photoInput.value?.click();
}

function onPhotoSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    captureGps(() => {
        const formData = new FormData();
        formData.append('photo', file);
        if (gpsCoords.value.lat) formData.append('lat', gpsCoords.value.lat);
        if (gpsCoords.value.lng) formData.append('lng', gpsCoords.value.lng);

        router.post(`/field-visits/${props.visit.id}/photos`, formData, {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => { event.target.value = ''; },
        });
    });
}

function completeVisit() {
    completeForm.post(`/field-visits/${props.visit.id}/complete`);
}

onMounted(startGpsTracking);
onUnmounted(() => { if (locationTimer) clearInterval(locationTimer); });
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-lg">
            <div class="mb-4">
                <h1 class="text-xl font-bold md:text-2xl">{{ visit.project ? displayName(visit.project) : t('quotations.field_visits') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ visit.project?.location }}</p>
                <p v-if="visit.employee && canManage" class="mt-1 text-xs text-cyan-400">
                    {{ t('hr.employees') }}: {{ displayName(visit.employee) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ formatDateTime(visit.visited_at) }}
                </p>
            </div>

            <div v-if="mapMarkers.length" class="mb-4 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-3">
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-cyan-400">
                        {{ canManage ? t('ui.gps') : t('ui.map') }}
                    </h2>
                    <span v-if="isActive && canManage" class="animate-pulse text-[10px] text-green-400">● {{ t('ui.live') }}</span>
                    <span v-else-if="isActive && isOwner && gpsTracking" class="text-[10px] text-green-400">● {{ t('ui.gps') }}</span>
                </div>
                <GpsMap :markers="mapMarkers" height="240px" />
                <div v-if="gpsCoords.lat" class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="font-mono text-green-400">{{ gpsCoords.lat.toFixed(5) }}, {{ gpsCoords.lng.toFixed(5) }}</span>
                    <a
                        :href="mapsUrl(gpsCoords.lat, gpsCoords.lng)"
                        target="_blank"
                        class="text-cyan-400 hover:underline"
                    >
                        Google Maps
                    </a>
                </div>
            </div>

            <div v-if="!isActive" class="mb-4 rounded-lg bg-green-500/10 px-4 py-3 text-sm text-green-400">
                {{ t('ui.completed') }}
            </div>

            <div v-if="isActive && isOwner" class="mb-6 rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                <h2 class="mb-3 font-semibold text-cyan-400">{{ t('ui.photo') }}</h2>
                <p class="mb-4 text-xs text-slate-500">{{ t('ui.gps') }}</p>

                <input ref="photoInput" type="file" accept="image/*" capture="environment" class="hidden" @change="onPhotoSelected" />

                <button
                    @click="takePhoto"
                    class="touch-target flex w-full items-center justify-center gap-3 rounded-xl border-2 border-dashed border-cyan-500/30 py-8 text-cyan-400 hover:border-cyan-400 hover:bg-cyan-500/5"
                >
                    <span class="text-3xl">📷</span>
                    <span class="font-bold">{{ t('ui.photo') }}</span>
                </button>

                <div v-if="visit.photos?.length" class="mt-4 grid grid-cols-2 gap-3">
                    <div v-for="photo in visit.photos" :key="photo.id" class="overflow-hidden rounded-lg border border-white/10">
                        <img :src="photo.watermarked_url || photo.original_url" class="aspect-square w-full object-cover" :alt="t('ui.photo')" />
                    </div>
                </div>
            </div>

            <div v-if="visit.photos?.length && (!isActive || canManage)" class="mb-6">
                <h2 v-if="canManage && isActive" class="mb-3 text-sm font-semibold text-slate-400">{{ t('ui.photo') }}</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div v-for="photo in visit.photos" :key="photo.id" class="overflow-hidden rounded-lg border border-white/10">
                        <img :src="photo.watermarked_url || photo.original_url" class="aspect-square w-full object-cover" :alt="t('ui.photo')" />
                    </div>
                </div>
            </div>

            <form v-if="isActive && isOwner" @submit.prevent="completeVisit" class="space-y-4 rounded-xl border border-green-500/15 bg-[#1a2540] p-5">
                <h2 class="font-semibold text-green-400">{{ t('ui.end') }}</h2>
                <textarea
                    v-model="completeForm.notes"
                    rows="3"
                    class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-4 py-3 text-sm outline-none focus:border-cyan-400"
                    :placeholder="t('ui.notes') + '...'"
                />
                <button type="submit" :disabled="completeForm.processing" class="touch-target w-full rounded-xl bg-green-500 py-4 text-base font-bold text-[#0a0f1e] hover:bg-green-400">
                    {{ t('ui.complete') }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
