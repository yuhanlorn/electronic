import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import { run } from "vite-plugin-run";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        run([
            {
                name: "wayfinder",
                run: ["php", "artisan", "wayfinder:generate", "--path=resources/js/wayfinder"],
                pattern: ["routes/**/*.php", "app/**/Http/**/*.php"],
            },
            {
                name: "typetransformer",
                run: ["php", "artisan", "typescript:transform"],
                pattern: ["app/Data/**/*.php", "app/Enums/**/*.php"],
            }
        ]),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
});
