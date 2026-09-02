const dateFormatter = new Intl.DateTimeFormat('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

export function formatPostDate(iso: string): string {
    return dateFormatter.format(new Date(iso));
}
