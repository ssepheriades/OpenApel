import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchSiteSettings, type SiteSettings } from '@/api/siteSettings';
import { applyBrandColors, type BrandThemeTarget } from '@/plugins/theme';

export const DEFAULT_SITE_NAME = 'OpenApel';

export type PageVisibilityKey = 'faqVisible' | 'teamVisible' | 'postsVisible' | 'agendaVisible';

const ROUTE_VISIBILITY: Record<string, PageVisibilityKey> = {
    faq: 'faqVisible',
    team: 'teamVisible',
    news: 'postsVisible',
    'news-detail': 'postsVisible',
    agenda: 'agendaVisible',
};

export const useAppStore = defineStore('app', () => {
    const apiUrl = ref<string>(import.meta.env.VITE_API_URL ?? '');

    const settings = ref<SiteSettings | null>(null);
    const settingsLoading = ref(false);
    const settingsError = ref<string | null>(null);

    const siteName = computed(() => settings.value?.siteName ?? DEFAULT_SITE_NAME);
    const baseline = computed(() => settings.value?.baseline ?? null);
    const homeTitle = computed(() => settings.value?.homeTitle ?? siteName.value);
    const homeText = computed(() => settings.value?.homeText ?? null);

    /**
     * Hidden pages stay reachable until settings load (or if they fail),
     * so a settings outage never blocks the public site.
     */
    function isRouteVisible(name: string | symbol | null | undefined): boolean {
        if (typeof name !== 'string') {
            return true;
        }

        const key = ROUTE_VISIBILITY[name];
        if (key === undefined) {
            return true;
        }

        return settings.value?.[key] ?? true;
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

    return { apiUrl, settings, settingsLoading, settingsError, siteName, baseline, homeTitle, homeText, isRouteVisible, loadSettings };
});
