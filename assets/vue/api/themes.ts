export type ContentTheme =
    | 'apprentissage'
    | 'sport'
    | 'culture'
    | 'alimentation'
    | 'extra_scolaire'
    | 'autre';

export const CONTENT_THEMES: ContentTheme[] = [
    'apprentissage',
    'sport',
    'culture',
    'alimentation',
    'extra_scolaire',
    'autre',
];

export const THEME_LABELS: Record<ContentTheme, string> = {
    apprentissage: 'Apprentissage',
    sport: 'Sport',
    culture: 'Culture',
    alimentation: 'Alimentation',
    extra_scolaire: 'Extra scolaire',
    autre: 'Autre',
};

export const THEME_ICONS: Record<ContentTheme, string> = {
    apprentissage: 'mdi-school',
    sport: 'mdi-soccer',
    culture: 'mdi-palette',
    alimentation: 'mdi-food-apple',
    extra_scolaire: 'mdi-puzzle',
    autre: 'mdi-dots-horizontal-circle',
};
