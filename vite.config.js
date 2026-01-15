// C:\laragon\www\laravel\guzanet-12.26\vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true, // Esto activa el HMR para Blade y Rutas
        }),
    ],
    server: {
        // Esto ayuda si usas Laragon con nombres de dominio .test
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true, // Útil en Windows para detectar cambios de archivos instantáneamente
        },
    },
});