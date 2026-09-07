import client from './client';
import type { ContentTheme } from './themes';

export type { ContentTheme };

export interface SiteDocument {
    id: number;
    name: string;
    description: string | null;
    date: string;
    theme: ContentTheme;
    fileUrl: string | null;
}

export async function fetchDocuments(): Promise<SiteDocument[]> {
    return client.request<SiteDocument[]>('/documents', {
        headers: { Accept: 'application/json' },
    });
}
