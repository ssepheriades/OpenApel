import { createRouter, createWebHistory } from 'vue-router';
import { useAppStore, type PageVisibilityKey } from '@/stores/app';

declare module 'vue-router' {
    interface RouteMeta {
        visibilityKey?: PageVisibilityKey;
    }
}

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
            meta: { visibilityKey: 'teamVisible' },
        },
        {
            path: '/agenda',
            name: 'agenda',
            component: () => import('@/pages/AgendaPage.vue'),
            meta: { visibilityKey: 'agendaVisible' },
        },
        {
            path: '/actualites',
            name: 'news',
            component: () => import('@/pages/NewsPage.vue'),
            meta: { visibilityKey: 'postsVisible' },
        },
        {
            path: '/actualites/:id',
            name: 'news-detail',
            component: () => import('@/pages/NewsDetailPage.vue'),
            meta: { visibilityKey: 'postsVisible' },
        },
        {
            path: '/faq',
            name: 'faq',
            component: () => import('@/pages/FaqPage.vue'),
            meta: { visibilityKey: 'faqVisible' },
        },
        {
            path: '/contact',
            name: 'contact',
            component: () => import('@/pages/ContactPage.vue'),
        },
    ],
});

router.beforeEach((to) => {
    if (to.meta.visibilityKey === undefined) {
        return true;
    }

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
