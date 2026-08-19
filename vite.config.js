import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(import.meta.dirname, './resources/js'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/vue/') || id.includes('node_modules/@vue/') || id.includes('node_modules/@inertiajs/')) {
                        return 'vendor-vue';
                    }
                    if (id.includes('node_modules/chart.js') || id.includes('node_modules/vue-chartjs')) {
                        return 'vendor-charts';
                    }
                    if (id.includes('node_modules/lucide-vue-next')) {
                        return 'vendor-icons';
                    }
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
