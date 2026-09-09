import type { Grade, SchoolClass } from './audience';
import client from './client';

export type EventType =
    | 'public_holiday'
    | 'vacation'
    | 'pedagogical_day'
    | 'party'
    | 'public_meeting'
    | 'school_event';
export type EventState = 'open' | 'full' | 'cancelled';
export type EventVisibility = 'visible' | 'hidden' | 'greyed_out';

export const SCHOOL_CLOSURE_TYPES: readonly EventType[] = [
    'vacation',
    'pedagogical_day',
    'public_holiday',
];

const AGENDA_DETAIL_DISABLED_TYPES: readonly EventType[] = ['vacation', 'public_holiday'];

export function isSchoolClosureEvent(type: EventType): boolean {
    return SCHOOL_CLOSURE_TYPES.includes(type);
}

export function hasAgendaDetail(type: EventType): boolean {
    return !AGENDA_DETAIL_DISABLED_TYPES.includes(type);
}

export interface Event {
    id: number;
    slug: string;
    title: string;
    description: string;
    shortDescription: string | null;
    startsAt: string;
    endsAt: string | null;
    location: string | null;
    ticketingUrl: string | null;
    type: EventType;
    state: EventState;
    visibility: EventVisibility | null;
    isAllDay?: boolean;
    grades: Grade[];
    schoolClasses: SchoolClass[];
    heroImageUrl?: string | null;
    flyerImageUrl?: string | null;
}

export async function fetchEvents(range?: { after: string; strictlyBefore: string }): Promise<Event[]> {
    const params = new URLSearchParams();
    if (range) {
        params.set('startsAt[after]', range.after);
        params.set('startsAt[strictly_before]', range.strictlyBefore);
    }

    const query = params.toString();

    return client.request<Event[]>(`/events${query ? `?${query}` : ''}`, {
        headers: { Accept: 'application/json' },
    });
}

export async function fetchEvent(slug: string): Promise<Event> {
    return client.request<Event>(`/events/${encodeURIComponent(slug)}`, {
        headers: { Accept: 'application/json' },
    });
}
