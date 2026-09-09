import { describe, expect, it } from 'vitest';
import { formatEventDate, formatEventDateRange, formatEventTime, isAllDayCurrent } from './eventDate';

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
        expect(label?.toLowerCase()).not.toMatch(/lun\.|mar\.|mer\.|jeu\.|ven\.|sam\.|dim\./u);
    });
});

describe('formatEventDateRange', () => {
    it('keeps a single day for all-day without end', () => {
        const label = formatEventDateRange({
            startsAt: '2026-10-12T00:00:00+02:00',
            endsAt: null,
            isAllDay: true,
        });

        expect(label.toLowerCase()).toContain('oct');
        expect(label).toMatch(/12/u);
        expect(label.toLowerCase()).toMatch(/lun\.|mar\.|mer\.|jeu\.|ven\.|sam\.|dim\./u);
        expect(label).not.toContain('–');
    });

    it('joins inclusive civil days for a multi-day all-day event without weekdays', () => {
        const label = formatEventDateRange({
            startsAt: '2026-10-12T00:00:00+02:00',
            endsAt: '2026-10-16T00:00:00+02:00',
            isAllDay: true,
        });

        expect(label).toContain('–');
        expect(label).toMatch(/12/u);
        expect(label).toMatch(/16/u);
        expect(label.toLowerCase()).not.toMatch(/lun\.|mar\.|mer\.|jeu\.|ven\.|sam\.|dim\./u);
    });
});

describe('isAllDayCurrent', () => {
    const noon = new Date('2026-09-12T12:00:00+02:00');

    it('keeps a same-day all-day event current after midnight start', () => {
        expect(
            isAllDayCurrent(
                {
                    startsAt: '2026-09-12T00:00:00+02:00',
                    endsAt: null,
                    isAllDay: true,
                },
                noon,
            ),
        ).toBe(true);
    });

    it('keeps a multi-day all-day event current until the inclusive end day', () => {
        expect(
            isAllDayCurrent(
                {
                    startsAt: '2026-09-10T00:00:00+02:00',
                    endsAt: '2026-09-12T00:00:00+02:00',
                    isAllDay: true,
                },
                noon,
            ),
        ).toBe(true);
        expect(
            isAllDayCurrent(
                {
                    startsAt: '2026-09-10T00:00:00+02:00',
                    endsAt: '2026-09-11T00:00:00+02:00',
                    isAllDay: true,
                },
                noon,
            ),
        ).toBe(false);
    });
});
