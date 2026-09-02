import type { Grade, SchoolClass } from './audience';
import client from './client';

export type EventType = 'public_holiday' | 'vacation' | 'party' | 'public_meeting' | 'school_event';
export type EventState = 'open' | 'full' | 'cancelled';
export type EventVisibility = 'visible' | 'hidden' | 'greyed_out';

export interface Event {
    id: number;
    title: string;
    description: string;
    shortDescription: string | null;
    startsAt: string;
    endsAt: string | null;
    location: string | null;
    ticketingUrl: string | null;
    type: EventType;
    state: EventState;
    visibility: EventVisibility;
    isAllDay: boolean | null;
    grades: Grade[];
    schoolClasses: SchoolClass[];
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
