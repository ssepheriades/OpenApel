import type { Grade, SchoolClass } from './audience';
import client from './client';
import type { ContentTheme } from './themes';

export interface Post {
    id: number;
    title: string;
    content: string;
    theme: ContentTheme;
    createdAt: string;
    grades: Grade[];
    schoolClasses: SchoolClass[];
}

export async function fetchPosts(): Promise<Post[]> {
    return client.request<Post[]>('/posts', {
        headers: { Accept: 'application/json' },
    });
}

export async function fetchPost(id: number): Promise<Post> {
    return client.request<Post>(`/posts/${id}`, {
        headers: { Accept: 'application/json' },
    });
}
