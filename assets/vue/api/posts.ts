import type { Grade, SchoolClass } from './audience';
import client from './client';
import type { ContentTheme } from './themes';

export interface Post {
    id: number;
    slug: string;
    title: string;
    content: string;
    theme: ContentTheme;
    createdAt: string;
    coverImageUrl?: string | null;
    grades: Grade[];
    schoolClasses: SchoolClass[];
}

export async function fetchPosts(): Promise<Post[]> {
    return client.request<Post[]>('/posts', {
        headers: { Accept: 'application/json' },
    });
}

export async function fetchPost(slug: string): Promise<Post> {
    return client.request<Post>(`/posts/${encodeURIComponent(slug)}`, {
        headers: { Accept: 'application/json' },
    });
}
