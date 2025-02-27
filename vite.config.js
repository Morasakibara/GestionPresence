import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // Ajoutez cette configuration pour désactiver l'overlay d'erreur si nécessaire
    server: {
        hmr: {
            overlay: false
        }
    }
});
