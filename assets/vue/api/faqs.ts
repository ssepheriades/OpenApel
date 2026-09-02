import type { Grade, SchoolClass } from './audience';
import client from './client';
import type { ContentTheme } from './themes';

export type { ContentTheme };

export interface Faq {
    id: number;
    question: string;
    answer: string;
    theme: ContentTheme;
    createdAt: string;
    grades: Grade[];
    schoolClasses: SchoolClass[];
}

export async function fetchFaqs(): Promise<Faq[]> {
    return client.request<Faq[]>('/faqs', {
        headers: { Accept: 'application/json' },
    });
}
