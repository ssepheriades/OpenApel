import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';
import symfonyPlugin from 'vite-plugin-symfony';

const isDdev = Boolean(process.env.DDEV_PRIMARY_URL_WITHOUT_PORT);

export default defineConfig({
    plugins: [
        vue(),
        vuetify({ autoImport: true }),
        symfonyPlugin({
            // localhost: browser → localhost:5173
            // DDEV: browser → https://openapel.ddev.site:5174
            viteDevServerHostname: process.env.DDEV_HOSTNAME || 'localhost',
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        // Required so asset URLs point at the DDEV-exposed Vite HTTPS port.
        ...(isDdev
            ? { origin: `${process.env.DDEV_PRIMARY_URL_WITHOUT_PORT}:5174` }
            : {
                  proxy: {
                      '^(?!/build)': {
                          target: 'http://localhost:8000',
                          changeOrigin: true,
                      },
                  },
              }),
        cors: isDdev
            ? {
                  origin: /https?:\/\/([A-Za-z0-9\-.]+)?(\.ddev\.site)(?::\d+)?$/,
              }
            : true,
        watch: {
            usePolling: true,
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
                admin: './assets/admin/markdown-editor.ts',
            },
        },
    },
});
