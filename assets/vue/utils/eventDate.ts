import { APP_TIMEZONE } from './timezone';

export interface EventSchedule {
    startsAt: string;
    endsAt: string | null;
    isAllDay?: boolean | null;
}

const dateFormatter = new Intl.DateTimeFormat('fr-FR', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    timeZone: APP_TIMEZONE,
});

const compactDateFormatter = new Intl.DateTimeFormat('fr-FR', {
    day: 'numeric',
    month: 'short',
    timeZone: APP_TIMEZONE,
});

const timeFormatter = new Intl.DateTimeFormat('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
    timeZone: APP_TIMEZONE,
});

const calendarDayFormatter = new Intl.DateTimeFormat('en-CA', {
    timeZone: APP_TIMEZONE,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
});

export function formatEventDate(iso: string): string {
    return dateFormatter.format(new Date(iso));
}

export function formatEventDateRange(event: EventSchedule): string {
    if (!event.endsAt || isSameLocalDay(event.startsAt, event.endsAt)) {
        return formatEventDate(event.startsAt);
    }

    return `${formatCompactDate(event.startsAt)} – ${formatCompactDate(event.endsAt)}`;
}

export function formatEventTime(event: EventSchedule): string | null {
    if (event.isAllDay) {
        return null;
    }

    const start = formatTime(event.startsAt);
    if (!event.endsAt) {
        return start;
    }

    const end = formatTime(event.endsAt);
    if (start === end && isSameLocalDay(event.startsAt, event.endsAt)) {
        return start;
    }

    if (isSameLocalDay(event.startsAt, event.endsAt)) {
        return `${start} – ${end}`;
    }

    return `${start} – ${formatCompactDate(event.endsAt)} ${end}`;
}

export function isAllDayCurrent(event: EventSchedule, now: Date = new Date()): boolean {
    const lastDay = event.endsAt ?? event.startsAt;

    return calendarDayFormatter.format(now) <= calendarDayFormatter.format(new Date(lastDay));
}

function formatCompactDate(iso: string): string {
    return compactDateFormatter.format(new Date(iso));
}

function formatTime(iso: string): string {
    return timeFormatter.format(new Date(iso));
}

function isSameLocalDay(left: string, right: string): boolean {
    return calendarDayFormatter.format(new Date(left)) === calendarDayFormatter.format(new Date(right));
}
