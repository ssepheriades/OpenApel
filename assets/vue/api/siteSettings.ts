import client from './client';

export interface SiteSettings {
    siteName: string;
    baseline: string | null;
    logoUrl: string | null;
    faviconUrl: string | null;
    contactEmail: string | null;
    facebookUrl: string | null;
    instagramUrl: string | null;
    primaryColor: string;
    secondaryColor: string;
    schoolYearStart: string;
    schoolYearEnd: string;
}

export async function fetchSiteSettings(): Promise<SiteSettings> {
    return client.request<SiteSettings>('/site_settings', {
        headers: { Accept: 'application/json' },
    });
}
