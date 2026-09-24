export const CALENDAR_FEED_PATH = '/calendar.ics';

export function eventIcsPath(slug: string): string {
    return `/calendar/events/${encodeURIComponent(slug)}.ics`;
}

export function calendarFeedUrl(origin: string = window.location.origin): string {
    return `${origin}${CALENDAR_FEED_PATH}`;
}

export function webcalUrl(httpsUrl: string): string {
    return httpsUrl.replace(/^https?:/i, 'webcal:');
}

export function googleCalendarSubscribeUrl(httpsUrl: string): string {
    return `https://calendar.google.com/calendar/render?cid=${encodeURIComponent(httpsUrl)}`;
}
