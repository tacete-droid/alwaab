import '../css/app.css';
import { initTheme } from './composables/useTheme';
import './echo';

initTheme();
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { initEcho } from './echo';

createInertiaApp({
    title: (title) => (title ? `${title} — ALWAAB` : 'ALWAAB ERP+CRM'),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        if (props.initialPage?.props?.auth?.user) {
            initEcho();
        }

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#00d4ff',
    },
});
