import client from './client';

export interface ContentTheme {
    id: number;
    name: string;
    icon: string;
}

export async function fetchContentThemes(): Promise<ContentTheme[]> {
    return client.request<ContentTheme[]>('/content_themes', {
        headers: { Accept: 'application/json' },
    });
}
