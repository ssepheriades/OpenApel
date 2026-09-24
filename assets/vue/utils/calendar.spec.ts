import { describe, expect, it } from 'vitest';
import {
    CALENDAR_FEED_PATH,
    calendarFeedUrl,
    eventIcsPath,
    googleCalendarSubscribeUrl,
    webcalUrl,
} from './calendar';

describe('calendar urls', () => {
    it('builds a same-origin feed url', () => {
        expect(CALENDAR_FEED_PATH).toBe('/calendar.ics');
        expect(calendarFeedUrl('https://asso.example.fr')).toBe('https://asso.example.fr/calendar.ics');
    });

    it('encodes the event slug in the ics path', () => {
        expect(eventIcsPath('assemblee-generale')).toBe('/calendar/events/assemblee-generale.ics');
    });

    it('converts https to webcal', () => {
        expect(webcalUrl('https://asso.example.fr/calendar.ics')).toBe('webcal://asso.example.fr/calendar.ics');
        expect(webcalUrl('http://localhost/calendar.ics')).toBe('webcal://localhost/calendar.ics');
    });

    it('builds a google calendar subscribe url', () => {
        expect(googleCalendarSubscribeUrl('https://asso.example.fr/calendar.ics')).toBe(
            'https://calendar.google.com/calendar/render?cid=https%3A%2F%2Fasso.example.fr%2Fcalendar.ics',
        );
    });
});
