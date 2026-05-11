import '../css/app.css';
import './bootstrap';
import 'vue-sonner/style.css';
import '@vuepic/vue-datepicker/dist/main.css';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, DefineComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/src/js';
import { createI18n } from 'vue-i18n';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/lv';
import 'dayjs/locale/en';
import lv from './locales/lv.json';
import en from './locales/en.json';

dayjs.extend(relativeTime);

const savedLocale = (localStorage.getItem('meistari_locale') as 'lv' | 'en') ?? 'lv';
dayjs.locale(savedLocale);
document.documentElement.lang = savedLocale;

const i18n = createI18n({
    legacy: false,
    locale: savedLocale,
    fallbackLocale: 'lv',
    messages: { lv, en },
});

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title: string) => `${title} - ${appName}`,
    resolve: (name: string) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#0a192f',
    },
});