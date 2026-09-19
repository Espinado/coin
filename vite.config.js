import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-support.js',
                'resources/js/admin-support-realtime.js',
                'resources/js/guest-support.js',
                'resources/js/admin-vox-call.js',
            ],
            refresh: true,
        }),
    ],
});
