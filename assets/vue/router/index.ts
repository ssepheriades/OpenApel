import { createRouter, createWebHistory } from 'vue-router';
import { useAppStore } from '@/stores/app';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: () => import('@/pages/HomePage.vue'),
        },
        {
            path: '/equipe',
            name: 'team',
            component: () => import('@/pages/TeamPage.vue'),
        },
        {
            path: '/agenda',
            name: 'agenda',
            component: () => import('@/pages/AgendaPage.vue'),
        },
        {
            path: '/actualites',
            name: 'news',
            component: () => import('@/pages/NewsPage.vue'),
        },
        {
            path: '/actualites/:id',
            name: 'news-detail',
            component: () => import('@/pages/NewsDetailPage.vue'),
        },
        {
            path: '/faq',
            name: 'faq',
            component: () => import('@/pages/FaqPage.vue'),
        },
        {
            path: '/contact',
            name: 'contact',
            component: () => import('@/pages/ContactPage.vue'),
        },
        {
            path: '/mentions-legales',
            name: 'mentions-legales',
            component: () => import('@/pages/DocumentPage.vue'),
        },
        {
            path: '/politique-de-confidentialite',
            name: 'politique-de-confidentialite',
            component: () => import('@/pages/DocumentPage.vue'),
        },
    ],
});

router.beforeEach((to) => {
    const store = useAppStore();
    if (store.isRouteVisible(to.name)) {
        return true;
    }

    return { name: 'home' };
});

// Un chunk lazy peut devenir introuvable (déploiement qui invalide les vieux hashes,
// dev server redémarré) : sans cela, vue-router avorte la navigation en silence.
router.onError((error: Error, to) => {
    if (error.message.includes('Failed to fetch dynamically imported module')) {
        window.location.assign(to.fullPath);
    }
});

export default router;
