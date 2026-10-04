import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // When deploying with index.php + assets at project root (shared hosting style),
        // output the Vite build directly to ./build instead of ./public/build
        outDir: 'build',
    },
});
