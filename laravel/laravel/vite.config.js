import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/log-viewer.css',
                'resources/js/log-viewer.js',
            ],
            refresh: true,
        }),
    ],
});
