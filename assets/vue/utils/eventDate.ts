export interface EventSchedule {
    startsAt: string;
    endsAt: string | null;
    isAllDay: boolean | null;
}

const dateFormatter = new Intl.DateTimeFormat('fr-FR', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
});

const timeFormatter = new Intl.DateTimeFormat('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
});

export function formatEventDate(iso: string): string {
    return dateFormatter.format(new Date(iso));
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

    return `${start} – ${formatEventDate(event.endsAt)} ${end}`;
}

function formatTime(iso: string): string {
    return timeFormatter.format(new Date(iso));
}

function isSameLocalDay(left: string, right: string): boolean {
    const a = new Date(left);
    const b = new Date(right);

    return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
}
