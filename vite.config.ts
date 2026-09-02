import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
    plugins: [
        vue(),
        vuetify({ autoImport: true }),
        symfonyPlugin(),
    ],
    server: {
        proxy: {
            '^(?!/build)': {
                target: 'http://localhost:8000',
                changeOrigin: true,
            },
        },
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./assets/vue', import.meta.url)),
        },
    },
    optimizeDeps: {
        // vite-plugin-vuetify (autoImport) découvre chaque composant Vuetify comme
        // nouvelle dépendance à la première visite d'une route lazy, ce qui déclenche
        // des ré-optimisations et des 504 "Outdated Optimize Dep" en dev.
        exclude: ['vuetify'],
    },
    build: {
        rollupOptions: {
            input: {
                app: './assets/vue/main.ts',
            },
        },
    },
});
