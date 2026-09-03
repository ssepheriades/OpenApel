import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import { useAppStore } from './stores/app';
import './plugins/fontawesome';
import '../styles/main.css';

async function bootstrap(): Promise<void> {
    const app = createApp(App);
    const pinia = createPinia();

    app.use(pinia);
    app.use(vuetify);

    // Resolve branding before the first navigation so visibility flags are known.
    await useAppStore(pinia).boot(vuetify);

    app.use(router);
    app.mount('#app');
}

void bootstrap();
