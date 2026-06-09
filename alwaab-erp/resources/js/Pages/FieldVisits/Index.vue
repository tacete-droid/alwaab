<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GpsMap from '@/Components/GpsMap.vue';
import { useLocale } from '@/composables/useLocale';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    visits: Object,
    filters: Object,
    statuses: Array,
    projects: Array,
    employees: Array,
    canManage: Boolean,
});

const { t, displayName, intlLocale } = useLocale();
const canCreate = computed(() => usePage().props.auth?.user?.permissions?.includes('visits.create'));

const status = ref(props.filters?.status || '');
const projectId = ref(props.filters?.project_id || '');
const employeeId = ref(props.filters?.employee_id || '');

function statusLabel(value) {
    return props.statuses.find((s) => s.value === value)?.label || value;
}

function applyFilters() {
    router.get('/field-visits', {
        status: status.value || undefined,
        project_id: projectId.value || undefined,
        employee_id: employeeId.value || undefined,
    }, { preserveState: true });
}

const statusColors = {
    in_progress: 'text-cyan-400',
    completed: 'text-green-400',
    cancelled: 'text-red-400',
};

const showGpsMap = ref(false);
const selectedVisit = ref(null);

const selectedMarkers = computed(() => {
    if (!selectedVisit.value?.lat || !selectedVisit.value?.lng) return [];
    const v = selectedVisit.value;
    return [{
        lat: Number(v.lat),
        lng: Number(v.lng),
        color: v.status === 'in_progress' ? '#22d3ee' : '#22c55e',
        popup: `<b>${v.project ? displayName(v.project) : '—'}</b><br>${v.employee ? displayName(v.employee) : ''}`,
    }];
});

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString(intlLocale.value);
}

function openGps(visit, event) {
    if (!props.canManage || !visit.lat || !visit.lng) return;
    event.preventDefault();
    event.stopPropagation();
    selectedVisit.value = visit;
    showGpsMap.value = true;
}

function closeGps() {
    showGpsMap.value = false;
    selectedVisit.value = null;
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 md:mb-6">
                <div>
                    <h1 class="text-xl font-bold md:text-2xl">{{ t('quotations.field_visits') }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ t('ui.count', { count: visits.total }) }}</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="canManage"
                        href="/field-visits/map"
                        class="touch-target rounded-lg bg-green-500/10 px-4 py-2.5 text-sm text-green-400 hover:bg-green-500/20"
                    >
                        ● {{ t('ui.gps') }} {{ t('ui.live') }}
                    </Link>
                    <Link href="/field-visits/map" class="touch-target rounded-lg border border-cyan-500/30 px-4 py-2.5 text-sm text-cyan-400 hover:bg-cyan-500/10">
                        {{ t('ui.map') }}
                    </Link>
                </div>
            </div>

            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-3">
                <select v-model="status" @change="applyFilters" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-3 text-sm outline-none focus:border-cyan-400 sm:w-36">
                    <option value="">{{ t('crm.all_statuses') }}</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
                <select v-model="projectId" @change="applyFilters" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-3 text-sm outline-none focus:border-cyan-400 sm:w-48">
                    <option value="">{{ t('crm.projects') }}</option>
                    <option v-for="p in projects" :key="p.id" :value="p.id">{{ displayName(p) }}</option>
                </select>
                <select v-model="employeeId" @change="applyFilters" class="w-full rounded-lg border border-cyan-500/20 bg-[#0f172a] px-3 py-3 text-sm outline-none focus:border-cyan-400 sm:w-40">
                    <option value="">{{ t('hr.employees') }}</option>
                    <option v-for="e in employees" :key="e.id" :value="e.id">{{ displayName(e) }}</option>
                </select>
            </div>

            <div class="space-y-4">
                <Link
                    v-for="visit in visits.data"
                    :key="visit.id"
                    :href="`/field-visits/${visit.id}`"
                    class="block rounded-xl border border-cyan-500/15 bg-[#1a2540] p-4 transition active:scale-[0.99] md:p-5 hover:border-cyan-400/30"
                >
                    <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold">{{ visit.project ? displayName(visit.project) : '—' }}</h3>
                            <p class="text-xs text-slate-500">{{ visit.project?.location }}</p>
                        </div>
                        <span class="rounded-full bg-cyan-500/10 px-3 py-1 text-xs" :class="statusColors[visit.status]">
                            {{ statusLabel(visit.status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                        <div>
                            <p class="text-xs text-slate-500">{{ t('hr.employees') }}</p>
                            <p>{{ visit.employee ? displayName(visit.employee) : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">{{ t('ui.date') }}</p>
                            <p>{{ formatDateTime(visit.visited_at) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">{{ t('ui.gps') }}</p>
                            <button
                                v-if="canManage && visit.lat && visit.lng"
                                @click="openGps(visit, $event)"
                                class="font-mono text-xs text-cyan-400 underline decoration-dotted hover:text-cyan-300"
                            >
                                {{ Number(visit.lat).toFixed(5) }}, {{ Number(visit.lng).toFixed(5) }}
                            </button>
                            <p v-else class="font-mono text-xs text-cyan-400">
                                {{ visit.lat && visit.lng ? `${visit.lat}, ${visit.lng}` : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">{{ t('ui.photo') }}</p>
                            <p>{{ visit.photos?.length || 0 }}</p>
                        </div>
                    </div>

                    <p v-if="visit.notes" class="mt-3 border-t border-white/5 pt-3 text-sm text-slate-400">{{ visit.notes }}</p>
                </Link>
            </div>

            <Link
                v-if="canCreate"
                href="/field-visits/create"
                class="fixed bottom-20 left-4 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-cyan-500 text-2xl font-bold text-[#0a0f1e] shadow-lg shadow-cyan-500/30 md:bottom-6 md:left-6"
            >
                +
            </Link>

            <p v-if="!visits.data?.length" class="py-12 text-center text-slate-500">{{ t('ui.no_visits') }}</p>

            <div v-if="visits.last_page > 1" class="mt-4 flex justify-center gap-2">
                <button
                    v-for="link in visits.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="link.url && router.get(link.url)"
                    class="rounded px-3 py-1 text-sm"
                    :class="link.active
                        ? 'bg-cyan-500 text-[#0a0f1e] font-bold'
                        : link.url
                            ? 'text-slate-400 hover:bg-white/5'
                            : 'text-slate-600 cursor-not-allowed'"
                    v-html="link.label"
                />
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="showGpsMap"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/70 p-4 backdrop-blur-sm sm:items-center"
                @click.self="closeGps"
            >
                <div class="w-full max-w-lg overflow-hidden rounded-xl border border-cyan-500/20 bg-[#0f172a] shadow-2xl">
                    <div class="flex items-center justify-between border-b border-cyan-500/10 px-5 py-4">
                        <div>
                            <h3 class="font-bold text-cyan-400">{{ t('ui.gps') }}</h3>
                            <p v-if="selectedVisit" class="text-xs text-slate-500">
                                {{ selectedVisit.project ? displayName(selectedVisit.project) : '' }}
                                — {{ selectedVisit.employee ? displayName(selectedVisit.employee) : '' }}
                            </p>
                        </div>
                        <button @click="closeGps" class="rounded-lg px-3 py-1 text-sm text-slate-400 hover:bg-white/5">✕</button>
                    </div>
                    <div class="p-4">
                        <GpsMap :markers="selectedMarkers" height="280px" />
                        <a
                            v-if="selectedVisit?.lat"
                            :href="`https://www.google.com/maps?q=${selectedVisit.lat},${selectedVisit.lng}`"
                            target="_blank"
                            class="mt-3 block text-center text-sm text-cyan-400 hover:underline"
                        >
                            Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
