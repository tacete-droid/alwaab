import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useLocale() {
    const page = usePage();

    const locale = computed(() => page.props.locale || 'ar');
    const dir = computed(() => (locale.value === 'ar' ? 'rtl' : 'ltr'));
    const isRtl = computed(() => dir.value === 'rtl');
    const intlLocale = computed(() => (locale.value === 'ar' ? 'ar-AE' : 'en-AE'));
    const translations = computed(() => page.props.translations || {});

    function t(key, replacements = {}) {
        const parts = key.split('.');
        let value = translations.value;

        for (const part of parts) {
            if (value === undefined || value === null) {
                return key;
            }
            value = value[part];
        }

        if (typeof value !== 'string') {
            return key;
        }

        let result = value;
        for (const [placeholder, replacement] of Object.entries(replacements)) {
            result = result.replace(`:${placeholder}`, String(replacement));
        }

        return result;
    }

    function displayName(entity) {
        if (!entity) return '';
        return locale.value === 'ar'
            ? (entity.name_ar || entity.name_en || '')
            : (entity.name_en || entity.name_ar || '');
    }

    function formatMoney(amount) {
        return Number(amount || 0).toLocaleString(intlLocale.value, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function formatNumber(amount) {
        return Number(amount || 0).toLocaleString(intlLocale.value);
    }

    function formatDate(date) {
        if (!date) return '—';
        return new Date(date).toLocaleDateString(intlLocale.value);
    }

    function enumLabel(group, value) {
        if (!value) return '';
        const translated = t(`${group}.${value}`);
        return translated !== `${group}.${value}` ? translated : String(value);
    }

    return {
        locale,
        dir,
        isRtl,
        intlLocale,
        t,
        enumLabel,
        displayName,
        formatMoney,
        formatNumber,
        formatDate,
    };
}
