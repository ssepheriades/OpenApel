import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { fetchSiteSettings, type SiteSettings } from '@/api/siteSettings';
import { DEFAULT_SITE_NAME, useAppStore } from '@/stores/app';

vi.mock('@/api/siteSettings', () => ({
    fetchSiteSettings: vi.fn(),
}));

const mockedFetch = vi.mocked(fetchSiteSettings);

const sample: SiteSettings = {
    siteName: 'APEL Démo',
    baseline: 'Ensemble pour nos enfants',
    homeTitle: 'Bienvenue à l\'école',
    homeText: '**Ensemble** pour nos enfants.',
    logoUrl: 'http://localhost/uploads/branding/logo.png',
    faviconUrl: null,
    contactEmail: 'contact@example.org',
    facebookUrl: null,
    instagramUrl: null,
    primaryColor: '#123456',
    secondaryColor: '#abcdef',
    schoolYearStart: '2000-08-01',
    schoolYearEnd: '2000-07-31',
    faqVisible: true,
    teamVisible: true,
    postsVisible: true,
    agendaVisible: true,
};

function fakeTheme() {
    return { theme: { themes: { value: { light: { colors: { primary: '#000000', secondary: '#000000', accent: '#000000' } } } } } };
}

describe('useAppStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        mockedFetch.mockReset();
        document.title = '';
    });

    it('falls back to the default site name before loading', () => {
        const store = useAppStore();

        expect(store.siteName).toBe(DEFAULT_SITE_NAME);
        expect(store.settings).toBeNull();
        expect(store.homeTitle).toBe(DEFAULT_SITE_NAME);
        expect(store.homeText).toBeNull();
    });

    it('loads settings, applies theme colors and document title', async () => {
        mockedFetch.mockResolvedValue(sample);
        const theme = fakeTheme();
        const store = useAppStore();

        const pending = store.loadSettings(theme);
        expect(store.settingsLoading).toBe(true);
        await pending;

        expect(store.settingsLoading).toBe(false);
        expect(store.settingsError).toBeNull();
        expect(store.siteName).toBe('APEL Démo');
        expect(store.baseline).toBe('Ensemble pour nos enfants');
        expect(store.homeTitle).toBe('Bienvenue à l\'école');
        expect(store.homeText).toBe('**Ensemble** pour nos enfants.');
        expect(document.title).toBe('APEL Démo');
        expect(theme.theme.themes.value.light.colors).toMatchObject({
            primary: '#123456',
            secondary: '#abcdef',
            accent: '#abcdef',
        });
    });

    it('falls back to the site name when homeTitle is empty', async () => {
        mockedFetch.mockResolvedValue({ ...sample, homeTitle: null, homeText: null });
        const store = useAppStore();

        await store.loadSettings();

        expect(store.homeTitle).toBe('APEL Démo');
        expect(store.homeText).toBeNull();
    });

    it('keeps defaults and records the error when the API fails', async () => {
        mockedFetch.mockRejectedValue(new Error('boom'));
        const store = useAppStore();

        await store.loadSettings(fakeTheme());

        expect(store.settingsLoading).toBe(false);
        expect(store.settingsError).toBe('boom');
        expect(store.siteName).toBe(DEFAULT_SITE_NAME);
        expect(store.homeTitle).toBe(DEFAULT_SITE_NAME);
        expect(store.homeText).toBeNull();
    });

    it('treats every route as visible before settings load', () => {
        const store = useAppStore();

        expect(store.isRouteVisible('home')).toBe(true);
        expect(store.isRouteVisible('faq')).toBe(true);
        expect(store.isRouteVisible('team')).toBe(true);
        expect(store.isRouteVisible('news')).toBe(true);
        expect(store.isRouteVisible('news-detail')).toBe(true);
        expect(store.isRouteVisible('agenda')).toBe(true);
    });

    it('hides gated routes when their visibility flag is off', async () => {
        mockedFetch.mockResolvedValue({ ...sample, postsVisible: false, faqVisible: false });
        const store = useAppStore();

        await store.loadSettings();

        expect(store.isRouteVisible('home')).toBe(true);
        expect(store.isRouteVisible('team')).toBe(true);
        expect(store.isRouteVisible('agenda')).toBe(true);
        expect(store.isRouteVisible('news')).toBe(false);
        expect(store.isRouteVisible('news-detail')).toBe(false);
        expect(store.isRouteVisible('faq')).toBe(false);
    });
});
