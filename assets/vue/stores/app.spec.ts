import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { fetchPages, type SitePage } from '@/api/pages';
import { fetchSiteSettings, type SiteSettings } from '@/api/siteSettings';
import { DEFAULT_SITE_NAME, useAppStore } from '@/stores/app';

vi.mock('@/api/siteSettings', () => ({
    fetchSiteSettings: vi.fn(),
}));

vi.mock('@/api/pages', async () => {
    const actual = await vi.importActual<typeof import('@/api/pages')>('@/api/pages');

    return {
        ...actual,
        fetchPages: vi.fn(),
    };
});

const mockedFetchSettings = vi.mocked(fetchSiteSettings);
const mockedFetchPages = vi.mocked(fetchPages);

const sampleSettings: SiteSettings = {
    siteName: 'APEL Démo',
    baseline: 'Ensemble pour nos enfants',
    logoUrl: 'http://localhost/uploads/branding/logo.png',
    faviconUrl: null,
    contactEmail: 'contact@example.org',
    facebookUrl: null,
    instagramUrl: null,
    primaryColor: '#123456',
    secondaryColor: '#abcdef',
    schoolYearStart: '2000-08-01',
    schoolYearEnd: '2000-07-31',
};

const samplePages: SitePage[] = [
    { slug: 'home', kind: 'section', title: "Bienvenue à l'école", subtitle: null, body: '**Ensemble** pour nos enfants.', visible: true },
    { slug: 'news', kind: 'section', title: 'Actualités', subtitle: "Les nouvelles de l'association", body: null, visible: true },
    { slug: 'agenda', kind: 'section', title: 'Agenda', subtitle: 'Les dates', body: null, visible: true },
    { slug: 'faq', kind: 'section', title: 'FAQ', subtitle: 'Les questions', body: null, visible: true },
    { slug: 'team', kind: 'section', title: 'Le bureau', subtitle: null, body: null, visible: true },
    { slug: 'contact', kind: 'section', title: 'Contact', subtitle: 'Écrivez-nous', body: null, visible: true },
    { slug: 'mentions-legales', kind: 'document', title: 'Mentions légales', subtitle: null, body: 'Éditeur.', visible: true },
    {
        slug: 'politique-de-confidentialite',
        kind: 'document',
        title: 'Politique de confidentialité',
        subtitle: null,
        body: 'Données.',
        visible: true,
    },
];

function fakeTheme() {
    return { theme: { themes: { value: { light: { colors: { primary: '#000000', secondary: '#000000', accent: '#000000' } } } } } };
}

describe('useAppStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        mockedFetchSettings.mockReset();
        mockedFetchPages.mockReset();
        mockedFetchPages.mockResolvedValue([]);
        document.title = '';
    });

    it('falls back to the default site name before loading', () => {
        const store = useAppStore();

        expect(store.siteName).toBe(DEFAULT_SITE_NAME);
        expect(store.settings).toBeNull();
        expect(store.pageContent('home').title).toBe(DEFAULT_SITE_NAME);
        expect(store.pageContent('home').body).toBeNull();
        expect(store.pageContent('team').title).toBe('Équipe');
        expect(store.pageForRoute('team')?.title).toBe('Équipe');
    });

    it('loads settings, applies theme colors and document title', async () => {
        mockedFetchSettings.mockResolvedValue(sampleSettings);
        mockedFetchPages.mockResolvedValue(samplePages);
        const theme = fakeTheme();
        const store = useAppStore();

        const pending = store.boot(theme);
        expect(store.settingsLoading).toBe(true);
        await pending;

        expect(store.settingsLoading).toBe(false);
        expect(store.settingsError).toBeNull();
        expect(store.siteName).toBe('APEL Démo');
        expect(store.baseline).toBe('Ensemble pour nos enfants');
        expect(store.pageContent('home').title).toBe("Bienvenue à l'école");
        expect(store.pageContent('home').body).toBe('**Ensemble** pour nos enfants.');
        expect(store.pageForRoute('team')?.title).toBe('Le bureau');
        expect(document.title).toBe('APEL Démo');
        expect(theme.theme.themes.value.light.colors).toMatchObject({
            primary: '#123456',
            secondary: '#abcdef',
            accent: '#abcdef',
        });
    });

    it('falls back to the site name when the home title is empty', async () => {
        mockedFetchSettings.mockResolvedValue(sampleSettings);
        mockedFetchPages.mockResolvedValue(
            samplePages.map((page) => (page.slug === 'home' ? { ...page, title: '', body: null } : page)),
        );
        const store = useAppStore();

        await store.boot();

        expect(store.pageContent('home').title).toBe('APEL Démo');
        expect(store.pageContent('home').body).toBeNull();
    });

    it('keeps branding defaults when the settings API fails', async () => {
        mockedFetchSettings.mockRejectedValue(new Error('boom'));
        mockedFetchPages.mockResolvedValue(samplePages);
        const store = useAppStore();

        await store.boot(fakeTheme());

        expect(store.settingsLoading).toBe(false);
        expect(store.settingsError).toBe('boom');
        expect(store.siteName).toBe(DEFAULT_SITE_NAME);
        expect(store.pageContent('home').title).toBe("Bienvenue à l'école");
        expect(store.pageContent('home').body).toBe('**Ensemble** pour nos enfants.');
    });

    it('uses the site name for home when both APIs fail', async () => {
        mockedFetchSettings.mockRejectedValue(new Error('boom'));
        mockedFetchPages.mockRejectedValue(new Error('pages down'));
        const store = useAppStore();

        await store.boot();

        expect(store.siteName).toBe(DEFAULT_SITE_NAME);
        expect(store.pageContent('home').title).toBe(DEFAULT_SITE_NAME);
        expect(store.pageContent('home').body).toBeNull();
        expect(store.pageContent('faq').title).toBe('FAQ');
    });

    it('uses built-in copy when the pages API fails', async () => {
        mockedFetchSettings.mockResolvedValue(sampleSettings);
        mockedFetchPages.mockRejectedValue(new Error('pages down'));
        const store = useAppStore();

        await store.boot();

        expect(store.pagesError).toBe('pages down');
        expect(store.pageContent('faq').title).toBe('FAQ');
        expect(store.pageContent('team').title).toBe('Équipe');
        expect(store.pageContent('contact').subtitle).toContain('Écrivez-nous');
        expect(store.documentPages).toHaveLength(2);
    });

    it('treats gated routes as visible before the catalogue loads', () => {
        const store = useAppStore();

        expect(store.isRouteVisible('home')).toBe(true);
        expect(store.isRouteVisible('faq')).toBe(true);
        expect(store.isRouteVisible('team')).toBe(true);
        expect(store.isRouteVisible('news')).toBe(true);
        expect(store.isRouteVisible('news-detail')).toBe(true);
        expect(store.isRouteVisible('agenda')).toBe(true);
    });

    it('hides gated routes when their page visible flag is off', async () => {
        mockedFetchSettings.mockResolvedValue(sampleSettings);
        mockedFetchPages.mockResolvedValue(
            samplePages.map((page) =>
                page.slug === 'news' || page.slug === 'faq' ? { ...page, visible: false } : page,
            ),
        );
        const store = useAppStore();

        await store.boot();

        expect(store.isRouteVisible('home')).toBe(true);
        expect(store.isRouteVisible('team')).toBe(true);
        expect(store.isRouteVisible('agenda')).toBe(true);
        expect(store.isRouteVisible('news')).toBe(false);
        expect(store.isRouteVisible('news-detail')).toBe(false);
        expect(store.isRouteVisible('faq')).toBe(false);
    });

    it('hides legal documents when their visible flag is off', async () => {
        mockedFetchSettings.mockResolvedValue(sampleSettings);
        mockedFetchPages.mockResolvedValue(
            samplePages.map((page) =>
                page.slug === 'mentions-legales' ? { ...page, visible: false } : page,
            ),
        );
        const store = useAppStore();

        await store.boot();

        expect(store.isRouteVisible('mentions-legales')).toBe(false);
        expect(store.isRouteVisible('politique-de-confidentialite')).toBe(true);
        expect(store.documentPages.map((page) => page.slug)).toEqual(['politique-de-confidentialite']);
    });
});
