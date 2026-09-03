import client from './client';

export type PageSlug =
    | 'home'
    | 'news'
    | 'agenda'
    | 'faq'
    | 'team'
    | 'contact'
    | 'mentions-legales'
    | 'politique-de-confidentialite';

export type PageKind = 'section' | 'document';

export interface SitePage {
    slug: PageSlug;
    kind: PageKind;
    title: string;
    subtitle: string | null;
    body: string | null;
    visible: boolean;
}

/**
 * Keep in sync with App\Enum\PageSlug defaults. Used when the catalogue API is down.
 */
export const PAGE_DEFAULTS: Record<PageSlug, Omit<SitePage, 'slug' | 'kind'>> = {
    home: { title: 'Accueil', subtitle: null, body: null, visible: true },
    news: { title: 'Actualités', subtitle: "Les nouvelles de l'association", body: null, visible: true },
    agenda: {
        title: 'Agenda',
        subtitle: "Découvrez tous les événements à venir et l'historique de nos activités",
        body: null,
        visible: true,
    },
    faq: { title: 'FAQ', subtitle: 'Les questions les plus fréquentes des familles', body: null, visible: true },
    team: { title: 'Équipe', subtitle: null, body: null, visible: true },
    contact: {
        title: 'Contact',
        subtitle: 'Une question ? Écrivez-nous, nous vous répondrons dès que possible.',
        body: null,
        visible: true,
    },
    'mentions-legales': {
        title: 'Mentions légales',
        subtitle: null,
        body: "À compléter par l'association.\n\nIndiquez l'éditeur du site, l'hébergeur et les mentions obligatoires.",
        visible: true,
    },
    'politique-de-confidentialite': {
        title: 'Politique de confidentialité',
        subtitle: null,
        body: "À compléter par l'association.\n\nDécrivez les données collectées, leur usage et les droits des familles.",
        visible: true,
    },
};

export const DOCUMENT_SLUGS: PageSlug[] = ['mentions-legales', 'politique-de-confidentialite'];

/** Slugs that can be hidden from the public site. Keep in sync with PageSlug::usesVisibility(). */
export const GATED_SLUGS: PageSlug[] = [
    'news',
    'agenda',
    'faq',
    'team',
    'mentions-legales',
    'politique-de-confidentialite',
];

export async function fetchPages(): Promise<SitePage[]> {
    return client.request<SitePage[]>('/pages', {
        headers: { Accept: 'application/json' },
    });
}
