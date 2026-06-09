import { computed, ref } from 'vue';

const theme = ref(localStorage.getItem('alwaab-theme') || 'dark');

function applyTheme(value) {
    theme.value = value === 'light' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', theme.value);
    localStorage.setItem('alwaab-theme', theme.value);
}

export function initTheme() {
    applyTheme(theme.value);
}

export function useTheme() {
    const isDark = computed(() => theme.value === 'dark');

    function toggleTheme() {
        applyTheme(isDark.value ? 'light' : 'dark');
    }

    function setTheme(value) {
        applyTheme(value);
    }

    return {
        theme,
        isDark,
        toggleTheme,
        setTheme,
    };
}
