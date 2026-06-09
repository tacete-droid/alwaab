<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useLocale } from '@/composables/useLocale';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    history: Object,
    credits: Number,
    creditCosts: Object,
    canManageCredits: Boolean,
    users: Array,
});

const { t, displayName, formatDate } = useLocale();
const activeTab = ref('text');

const tabs = computed(() => [
    { id: 'text', label: t('ai_studio.tab_text'), icon: '📝', cost: props.creditCosts.text },
    { id: 'image', label: t('ai_studio.tab_image'), icon: '🖼️', cost: props.creditCosts.image },
    { id: 'video', label: t('ai_studio.tab_video'), icon: '🎬', cost: props.creditCosts.video },
]);

const form = useForm({ prompt: '', reference_file: null });
const creditForm = useForm({ user_id: '', amount: 10 });
const referencePreview = ref(null);
const referenceName = ref('');

function canGenerate(cost) {
    return props.credits >= cost;
}

function submit() {
    const routes = { text: '/ai-studio/text', image: '/ai-studio/image', video: '/ai-studio/video' };
    const cost = props.creditCosts[activeTab.value];

    if (!canGenerate(cost)) return;

    form.post(routes[activeTab.value], {
        preserveScroll: true,
        forceFormData: activeTab.value !== 'text',
        onSuccess: () => {
            form.reset('prompt', 'reference_file');
            clearReference();
        },
    });
}

function onReferenceChange(event) {
    const file = event.target.files[0];
    if (!file) return;
    form.reference_file = file;
    referenceName.value = file.name;
    if (file.type.startsWith('image/')) {
        referencePreview.value = URL.createObjectURL(file);
    } else {
        referencePreview.value = null;
    }
}

function clearReference() {
    form.reference_file = null;
    referenceName.value = '';
    if (referencePreview.value) {
        URL.revokeObjectURL(referencePreview.value);
        referencePreview.value = null;
    }
}

const showReferenceUpload = computed(() => activeTab.value === 'image' || activeTab.value === 'video');
const referenceHint = computed(() =>
    activeTab.value === 'image' ? t('ai_studio.reference_hint_image') : t('ai_studio.reference_hint_video')
);

function topUpCredits() {
    creditForm.post('/ai-studio/credits/top-up', { preserveScroll: true, onSuccess: () => creditForm.reset('amount') });
}

function statusClass(status) {
    return {
        processing: 'bg-amber-500/15 text-amber-400',
        completed: 'bg-green-500/15 text-green-400',
        failed: 'bg-red-500/15 text-red-400',
    }[status] || 'bg-slate-500/15 text-slate-400';
}

function refreshHistory() {
    router.reload({ only: ['history', 'credits'] });
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-6xl">
            <!-- Header -->
            <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('ai_studio.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-400">{{ t('ai_studio.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-violet-500/20 bg-violet-500/5 px-4 py-3">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <p class="text-xs text-slate-500">{{ t('ai_studio.credits') }}</p>
                        <p class="text-lg font-bold text-violet-400">{{ credits }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Generator Panel -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="overflow-hidden rounded-xl border border-slate-700/50 bg-[#1a2540] shadow-lg">
                        <!-- Tabs (shadcn-style) -->
                        <div class="flex border-b border-slate-700/50 bg-[#0f172a]/50 p-1">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                type="button"
                                @click="activeTab = tab.id"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition"
                                :class="activeTab === tab.id
                                    ? 'bg-violet-500/20 text-violet-300 shadow-sm'
                                    : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'"
                            >
                                <span>{{ tab.icon }}</span>
                                <span>{{ tab.label }}</span>
                                <span v-if="tab.cost > 0" class="rounded-full bg-slate-700/50 px-2 py-0.5 text-[10px]">{{ tab.cost }}⚡</span>
                            </button>
                        </div>

                        <!-- Prompt form -->
                        <form @submit.prevent="submit" class="p-5">
                            <textarea
                                v-model="form.prompt"
                                rows="5"
                                :placeholder="t('ai_studio.prompt_placeholder')"
                                class="w-full resize-none rounded-lg border border-slate-600/50 bg-[#0f172a] px-4 py-3 text-sm outline-none transition focus:border-violet-500/50 focus:ring-2 focus:ring-violet-500/20"
                                required
                            />

                            <div v-if="showReferenceUpload" class="mt-4 rounded-lg border border-dashed border-violet-500/30 bg-violet-500/5 p-4">
                                <label class="mb-2 block text-xs font-medium text-violet-300">{{ t('ai_studio.reference_upload') }}</label>
                                <p class="mb-3 text-[11px] text-slate-500">{{ referenceHint }}</p>
                                <input
                                    type="file"
                                    accept="image/*,video/mp4,video/webm,video/quicktime"
                                    class="w-full text-xs text-slate-400 file:me-3 file:rounded-lg file:border-0 file:bg-violet-600 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-white hover:file:bg-violet-500"
                                    @change="onReferenceChange"
                                />
                                <div v-if="referenceName" class="mt-3 flex items-center gap-3 rounded-lg bg-[#0f172a]/80 p-2">
                                    <img v-if="referencePreview" :src="referencePreview" alt="" class="h-14 w-14 rounded-lg object-cover" />
                                    <div v-else class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-800 text-lg">🎬</div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs text-violet-300">{{ t('ai_studio.reference_selected') }}</p>
                                        <p class="truncate text-[10px] text-slate-500">{{ referenceName }}</p>
                                    </div>
                                    <button type="button" class="text-xs text-red-400 hover:underline" @click="clearReference">{{ t('ai_studio.reference_remove') }}</button>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                <p class="text-xs text-slate-500">
                                    <template v-if="creditCosts[activeTab] > 0">
                                        {{ t('ai_studio.credit_cost', { count: creditCosts[activeTab] }) }}
                                    </template>
                                    <template v-else>{{ t('ai_studio.tab_text') }} — Free</template>
                                </p>
                                <button
                                    type="submit"
                                    :disabled="form.processing || !canGenerate(creditCosts[activeTab])"
                                    class="rounded-lg bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    {{ form.processing ? t('ai_studio.generating') : t('ai_studio.generate') }}
                                </button>
                            </div>
                            <p v-if="!canGenerate(creditCosts[activeTab])" class="mt-2 text-xs text-red-400">
                                {{ t('ai_studio.insufficient_credits') }}
                            </p>
                        </form>
                    </div>

                    <!-- Admin credits -->
                    <div v-if="canManageCredits" class="rounded-xl border border-cyan-500/15 bg-[#1a2540] p-5">
                        <h3 class="mb-4 font-semibold text-cyan-400">{{ t('ai_studio.admin_credits') }}</h3>
                        <form @submit.prevent="topUpCredits" class="flex flex-wrap gap-3">
                            <select v-model="creditForm.user_id" class="min-w-[200px] flex-1 rounded-lg border border-slate-600/50 bg-[#0f172a] px-3 py-2 text-sm outline-none" required>
                                <option value="">{{ t('ai_studio.top_up_for') }}</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">
                                    {{ displayName(u) }} ({{ u.ai_credits }}⚡)
                                </option>
                            </select>
                            <input v-model="creditForm.amount" type="number" min="1" class="w-28 rounded-lg border border-slate-600/50 bg-[#0f172a] px-3 py-2 text-sm outline-none" required />
                            <button type="submit" :disabled="creditForm.processing" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500 disabled:opacity-50">
                                {{ t('ai_studio.top_up_credits') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- History -->
                <div class="rounded-xl border border-slate-700/50 bg-[#1a2540] shadow-lg">
                    <div class="flex items-center justify-between border-b border-slate-700/50 px-4 py-3">
                        <h3 class="font-semibold text-slate-200">{{ t('ai_studio.history') }}</h3>
                        <button type="button" @click="refreshHistory" class="text-xs text-violet-400 hover:underline">↻</button>
                    </div>
                    <div class="max-h-[520px] overflow-y-auto p-3 space-y-2">
                        <div
                            v-for="item in history.data"
                            :key="item.id"
                            class="rounded-lg border border-slate-700/30 bg-[#0f172a]/60 p-3 text-sm"
                        >
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="text-xs font-medium text-violet-400">{{ t('ai_studio.types.' + item.type) }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium" :class="statusClass(item.status)">
                                    {{ t('ai_studio.status.' + item.status) }}
                                </span>
                            </div>
                            <p class="mb-2 line-clamp-2 text-xs text-slate-400">{{ item.prompt }}</p>
                            <div v-if="item.reference_url && item.reference_type === 'image'" class="mb-2">
                                <p class="mb-1 text-[10px] text-slate-600">{{ t('ai_studio.reference_upload') }}</p>
                                <img :src="item.reference_url" alt="" class="max-h-16 rounded object-cover opacity-80" />
                            </div>
                            <p v-else-if="item.reference_name" class="mb-2 text-[10px] text-slate-600">📎 {{ item.reference_name }}</p>
                            <p class="mb-2 text-[10px] text-slate-600">{{ formatDate(item.created_at) }}</p>

                            <div v-if="item.type === 'text' && item.output" class="mb-2 max-h-24 overflow-y-auto rounded bg-slate-800/50 p-2 text-xs text-slate-300">
                                {{ item.output }}
                            </div>

                            <div v-if="item.type === 'image' && item.download_url && item.status === 'completed'" class="mb-2">
                                <img :src="item.download_url" :alt="item.prompt" class="max-h-32 rounded-lg object-cover" />
                            </div>

                            <div class="flex gap-2">
                                <a
                                    v-if="item.download_url && item.status === 'completed'"
                                    :href="`/ai-studio/contents/${item.id}/download`"
                                    class="text-xs text-cyan-400 hover:underline"
                                >
                                    {{ t('ai_studio.download') }}
                                </a>
                            </div>
                            <p v-if="item.error" class="mt-1 text-[10px] text-red-400">{{ item.error }}</p>
                        </div>
                        <p v-if="!history.data?.length" class="py-8 text-center text-sm text-slate-500">{{ t('ai_studio.no_history') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
