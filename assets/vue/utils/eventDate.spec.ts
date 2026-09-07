import { describe, expect, it } from 'vitest';
import { formatEventDate, formatEventTime } from './eventDate';

describe('formatEventDate', () => {
    it('formats a French short date in Europe/Paris', () => {
        expect(formatEventDate('2026-09-08T18:00:00+02:00')).toMatch(/8/u);
        expect(formatEventDate('2026-09-08T18:00:00+02:00').toLowerCase()).toContain('sept');
    });
});

describe('formatEventTime', () => {
    it('returns null for all-day events', () => {
        expect(
            formatEventTime({
                startsAt: '2026-09-01T00:00:00+02:00',
                endsAt: '2026-09-01T23:59:00+02:00',
                isAllDay: true,
            }),
        ).toBeNull();
    });

    it('formats a start time when there is no end', () => {
        const label = formatEventTime({
            startsAt: '2026-09-08T18:00:00+02:00',
            endsAt: null,
            isAllDay: false,
        });

        expect(label).toMatch(/18/u);
        expect(label).toMatch(/00/u);
    });

    it('keeps the civil clock time when the API sent UTC by mistake', () => {
        const label = formatEventTime({
            startsAt: '2026-09-08T16:00:00+00:00',
            endsAt: null,
            isAllDay: false,
        });

        expect(label).toMatch(/18/u);
        expect(label).toMatch(/00/u);
    });

    it('formats a same-day range', () => {
        const label = formatEventTime({
            startsAt: '2026-09-08T18:00:00+02:00',
            endsAt: '2026-09-08T20:00:00+02:00',
            isAllDay: false,
        });

        expect(label).toContain('–');
        expect(label).toMatch(/18/u);
        expect(label).toMatch(/20/u);
    });

    it('includes the end date when the event spans several days', () => {
        const label = formatEventTime({
            startsAt: '2026-05-28T00:13:00+02:00',
            endsAt: '2026-05-30T17:13:00+02:00',
            isAllDay: false,
        });

        expect(label).toContain('–');
        expect(label?.toLowerCase()).toContain('mai');
    });
});
