import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';

export default defineConfig({
    resolve: {
        alias: {
            'lodash': 'lodash-es',
        },
        dedupe: [
            'axios',
            'lodash-es',
            'vue',
            '@inertiajs/core',
            '@inertiajs/vue3'
        ],
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        inertia(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('prime')) {
                            return 'vendor-primevue';
                        }
                        if (id.includes('fortawesome')) {
                            return 'vendor-fontawesome';
                        }
                        if (id.includes('axios')) {
                            return 'vendor-axios';
                        }
                        if (id.includes('lodash')) {
                            return 'vendor-lodash';
                        }
                        return 'vendor';
                    }
                },
            },
        },
    },
});
