import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import axios from 'axios';
import VueShortkey from 'vue-three-shortkey';
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import DaisyPrimePreset from './daisy-prime';
import { faGaugeHigh, faUsers } from '@fortawesome/free-solid-svg-icons';
import { configureEcho } from "@laravel/echo-vue";

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

configureEcho({ broadcaster: "reverb" });

const appName = import.meta.env.VITE_APP_NAME || 'Admin Panel';

library.add(faGaugeHigh, faUsers)

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    withApp(app) {
        app
            .use(VueShortkey)
            .use(ToastService)
            .use(PrimeVue, {
                theme: {
                    preset: DaisyPrimePreset,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '.app-dark',
                        cssLayer: false
                    }
                },
            })
            .component('fa', FontAwesomeIcon);
    },
    progress: {
        color: '#4B5563',
    },
});
