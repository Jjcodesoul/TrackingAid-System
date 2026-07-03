import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(), 
    ],
    server: {
        host: true, // 👈 Allows access from other local network devices dynamically
        hmr: {
            host: undefined, // 👈 Let Vite automatically handle the hot reload IP
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});