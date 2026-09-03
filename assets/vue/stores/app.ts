import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import {
    DOCUMENT_SLUGS,
    fetchPages,
    GATED_SLUGS,
    PAGE_DEFAULTS,
    type PageSlug,
    type SitePage,
} from '@/api/pages';
import { fetchSiteSettings, type SiteSettings } from '@/api/siteSettings';
import { applyBrandColors, type BrandThemeTarget } from '@/plugins/theme';

export const DEFAULT_SITE_NAME = 'OpenApel';

const ROUTE_PAGE_SLUG: Partial<Record<string, PageSlug>> = {
    home: 'home',
    news: 'news',
    'news-detail': 'news',
    agenda: 'agenda',
    faq: 'faq',
    team: 'team',
    contact: 'contact',
    'mentions-legales': 'mentions-legales',
    'politique-de-confidentialite': 'politique-de-confidentialite',
};

export const useAppStore = defineStore('app', () => {
    const apiUrl = ref<string>(import.meta.env.VITE_API_URL || '/api');

    const settings = ref<SiteSettings | null>(null);
    const settingsLoading = ref(false);
    const settingsError = ref<string | null>(null);

    const pages = ref<Partial<Record<PageSlug, SitePage>>>({});
    const pagesLoading = ref(false);
    const pagesError = ref<string | null>(null);

    const siteName = computed(() => settings.value?.siteName ?? DEFAULT_SITE_NAME);
    const baseline = computed(() => settings.value?.baseline ?? null);

    const documentPages = computed(() =>
        DOCUMENT_SLUGS.map((slug) => pageContent(slug)).filter((page) => page.visible),
    );

    /**
     * Hidden pages stay reachable until the catalogue loads (or if it fails),
     * so a pages outage never blocks the public site.
     */
    function isRouteVisible(name: string | symbol | null | undefined): boolean {
        if (typeof name !== 'string') {
            return true;
        }

        const slug = ROUTE_PAGE_SLUG[name];
        if (slug === undefined || !GATED_SLUGS.includes(slug)) {
            return true;
        }

        return pageContent(slug).visible;
    }

    function pageContent(slug: PageSlug): SitePage {
        const loaded = pages.value[slug];
        const defaults = PAGE_DEFAULTS[slug];
        const kind = slug === 'mentions-legales' || slug === 'politique-de-confidentialite' ? 'document' : 'section';
        const visible = loaded?.visible ?? defaults.visible;

        if (slug === 'home') {
            return {
                slug,
                kind,
                title: loaded?.title?.trim() || siteName.value,
                subtitle: null,
                body: loaded?.body ?? null,
                visible,
            };
        }

        return {
            slug,
            kind,
            title: loaded?.title?.trim() || defaults.title,
            subtitle: loaded?.subtitle ?? defaults.subtitle,
            body: loaded?.body ?? defaults.body,
            visible,
        };
    }

    function pageForRoute(name: string | symbol | null | undefined): SitePage | null {
        if (typeof name !== 'string') {
            return null;
        }

        const slug = ROUTE_PAGE_SLUG[name];
        if (slug === undefined) {
            return null;
        }

        return pageContent(slug);
    }

    /**
     * Loads the branding once at boot. On failure the app keeps its built-in
     * defaults so a settings outage never blocks the public site.
     */
    async function loadSettings(theme?: BrandThemeTarget): Promise<void> {
        settingsLoading.value = true;
        settingsError.value = null;

        try {
            const loaded = await fetchSiteSettings();
            settings.value = loaded;

            if (theme) {
                applyBrandColors(theme, loaded.primaryColor, loaded.secondaryColor);
            }

            if (typeof document !== 'undefined') {
                document.title = loaded.siteName;
            }
        } catch (error) {
            settingsError.value = error instanceof Error ? error.message : 'Unable to load site settings';
        } finally {
            settingsLoading.value = false;
        }
    }

    async function loadPages(): Promise<void> {
        pagesLoading.value = true;
        pagesError.value = null;

        try {
            const loaded = await fetchPages();
            const next: Partial<Record<PageSlug, SitePage>> = {};
            for (const page of loaded) {
                next[page.slug] = page;
            }
            pages.value = next;
        } catch (error) {
            pagesError.value = error instanceof Error ? error.message : 'Unable to load pages';
        } finally {
            pagesLoading.value = false;
        }
    }

    async function boot(theme?: BrandThemeTarget): Promise<void> {
        await Promise.all([loadSettings(theme), loadPages()]);
    }

    return {
        apiUrl,
        settings,
        settingsLoading,
        settingsError,
        pages,
        pagesLoading,
        pagesError,
        siteName,
        baseline,
        documentPages,
        isRouteVisible,
        pageContent,
        pageForRoute,
        loadSettings,
        loadPages,
        boot,
    };
});
