<script setup>
import AppToolbar from '@/Components/AppToolbar.vue';
import CompanyLogo from '@/Components/CompanyLogo.vue';
import { useLocale } from '@/composables/useLocale';
import { useForm } from '@inertiajs/vue3';

const { dir, t } = useLocale();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login');
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-app p-4" :dir="dir">
        <div class="absolute end-4 top-4 z-10 safe-top">
            <AppToolbar :show-notifications="false" />
        </div>
        <div class="w-full max-w-md">

            <div class="mb-8 flex flex-col items-center text-center">
                <CompanyLogo size="lg" class="mb-3" />
                <p class="text-sm text-slate-400">{{ t('crm.login_subtitle') }}</p>
            </div>

            <form
                @submit.prevent="submit"
                class="theme-scope rounded-2xl border border-app bg-app-card p-8 shadow-xl"
            >
                <h1 class="mb-6 text-lg font-bold">{{ t('crm.login_title') }}</h1>

                <div class="mb-4">
                    <label class="mb-1 block text-xs text-slate-400">{{ t('ui.email') }}</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-lg border border-app bg-app-input px-4 py-2.5 text-sm outline-none focus:border-cyan-400"
                        placeholder="admin@alwaab.ae"
                        required
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
                </div>

                <div class="mb-4">
                    <label class="mb-1 block text-xs text-slate-400">{{ t('ui.password') }}</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full rounded-lg border border-app bg-app-input px-4 py-2.5 text-sm outline-none focus:border-cyan-400"
                        required
                    />
                </div>

                <label class="mb-6 flex items-center gap-2 text-xs text-slate-400">
                    <input v-model="form.remember" type="checkbox" class="rounded" />
                    {{ t('crm.remember_me') }}
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-cyan-500 py-2.5 text-sm font-bold text-[#0a0f1e] transition hover:bg-cyan-400 disabled:opacity-50"
                >
                    {{ form.processing ? t('ui.signing_in') : t('ui.sign_in') }}
                </button>
            </form>

            <p class="mt-4 text-center text-xs text-slate-500">
                {{ t('layout.demo_hint') }}
            </p>
        </div>
    </div>
</template>
