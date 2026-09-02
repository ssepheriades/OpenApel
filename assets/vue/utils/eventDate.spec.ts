import { describe, expect, it } from 'vitest';
import { formatEventDate, formatEventTime } from './eventDate';

function localIso(year: number, month: number, day: number, hour = 0, minute = 0): string {
    return new Date(year, month - 1, day, hour, minute).toISOString();
}

describe('formatEventDate', () => {
    it('formats a French short date', () => {
        expect(formatEventDate(localIso(2026, 9, 8, 18, 0))).toMatch(/8/u);
        expect(formatEventDate(localIso(2026, 9, 8, 18, 0)).toLowerCase()).toContain('sept');
    });
});

describe('formatEventTime', () => {
    it('returns null for all-day events', () => {
        expect(
            formatEventTime({
                startsAt: localIso(2026, 9, 1),
                endsAt: localIso(2026, 9, 1, 23, 59),
                isAllDay: true,
            }),
        ).toBeNull();
    });

    it('formats a start time when there is no end', () => {
        const label = formatEventTime({
            startsAt: localIso(2026, 9, 8, 18, 0),
            endsAt: null,
            isAllDay: false,
        });

        expect(label).toMatch(/18/u);
        expect(label).toMatch(/00/u);
    });

    it('formats a same-day range', () => {
        const label = formatEventTime({
            startsAt: localIso(2026, 9, 8, 18, 0),
            endsAt: localIso(2026, 9, 8, 20, 0),
            isAllDay: false,
        });

        expect(label).toContain('–');
        expect(label).toMatch(/18/u);
        expect(label).toMatch(/20/u);
    });

    it('includes the end date when the event spans several days', () => {
        const label = formatEventTime({
            startsAt: localIso(2026, 5, 28, 0, 13),
            endsAt: localIso(2026, 5, 30, 17, 13),
            isAllDay: false,
        });

        expect(label).toContain('–');
        expect(label?.toLowerCase()).toContain('mai');
    });
});
