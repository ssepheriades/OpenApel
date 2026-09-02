import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import { createVuetify } from 'vuetify';

export default createVuetify({
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: { mdi },
    },
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#272857',
                    secondary: '#2ed8ff',
                    accent: '#2ed8ff',
                    error: '#ff5252',
                    warning: '#ffa726',
                    info: '#29b6f6',
                    success: '#66bb6a',
                    background: '#f5f5f5',
                    surface: '#ffffff',
                },
            },
        },
    },
});
