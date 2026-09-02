import client from './client';

export interface SiteSettings {
    siteName: string;
    baseline: string | null;
    homeTitle: string | null;
    homeText: string | null;
    logoUrl: string | null;
    faviconUrl: string | null;
    contactEmail: string | null;
    facebookUrl: string | null;
    instagramUrl: string | null;
    primaryColor: string;
    secondaryColor: string;
    schoolYearStart: string;
    schoolYearEnd: string;
    faqVisible: boolean;
    teamVisible: boolean;
    postsVisible: boolean;
    agendaVisible: boolean;
}

export async function fetchSiteSettings(): Promise<SiteSettings> {
    return client.request<SiteSettings>('/site_settings', {
        headers: { Accept: 'application/json' },
    });
}
