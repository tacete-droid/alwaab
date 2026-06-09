<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    size: { type: String, default: 'md' }, // sm | md | lg
    showName: { type: Boolean, default: true },
    variant: { type: String, default: 'dark' }, // dark | light
});

const page = usePage();
const company = computed(() => page.props.company || {});
const locale = computed(() => page.props.locale || 'ar');

const logoSrc = computed(() => company.value.logo || '/images/alwaab-logo.png');
const companyName = computed(() =>
    locale.value === 'ar'
        ? (company.value.name_ar || 'AL WAAB')
        : (company.value.name_en || 'AL WAAB')
);

const sizeClass = computed(() => ({
    sm: 'h-8',
    md: 'h-10',
    lg: 'h-16',
}[props.size] || 'h-10'));

const nameClass = computed(() =>
    props.variant === 'light' ? 'text-white' : 'text-cyan-400'
);
</script>

<template>
    <div class="flex items-center gap-2.5">
        <img :src="logoSrc" :alt="companyName" :class="[sizeClass, 'w-auto object-contain']" />
        <span v-if="showName" class="font-bold leading-tight" :class="[nameClass, size === 'lg' ? 'text-lg' : 'text-sm']">
            {{ companyName }}
        </span>
    </div>
</template>
