import { APP_TIMEZONE } from './timezone';

const dateFormatter = new Intl.DateTimeFormat('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: APP_TIMEZONE,
});

export function formatPostDate(iso: string): string {
    return dateFormatter.format(new Date(iso));
}
