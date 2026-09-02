import { describe, expect, it } from 'vitest';
import {
    DEFAULT_SCHOOL_YEAR_END,
    DEFAULT_SCHOOL_YEAR_START,
    findNextEventId,
    getSchoolYearRange,
    isUpcomingEvent,
    parseMonthDay,
    toIsoDate,
} from './schoolYear';

describe('parseMonthDay', () => {
    it('reads month and day from an ISO date', () => {
        expect(parseMonthDay('2000-08-01')).toEqual({ month: 8, day: 1 });
        expect(parseMonthDay('2026-07-31')).toEqual({ month: 7, day: 31 });
    });

    it('falls back when the value is missing or invalid', () => {
        expect(parseMonthDay(null, DEFAULT_SCHOOL_YEAR_END)).toEqual(DEFAULT_SCHOOL_YEAR_END);
        expect(parseMonthDay('not-a-date', DEFAULT_SCHOOL_YEAR_START)).toEqual(DEFAULT_SCHOOL_YEAR_START);
        expect(parseMonthDay('2026-13-01', DEFAULT_SCHOOL_YEAR_START)).toEqual(DEFAULT_SCHOOL_YEAR_START);
    });
});

describe('getSchoolYearRange', () => {
    it('starts a new year on 1 August', () => {
        const range = getSchoolYearRange(new Date(2026, 7, 1));

        expect(toIsoDate(range.start)).toBe('2026-08-01');
        expect(toIsoDate(range.endExclusive)).toBe('2027-08-01');
    });

    it('keeps September inside the year that started in August', () => {
        const range = getSchoolYearRange(new Date(2026, 8, 1));

        expect(toIsoDate(range.start)).toBe('2026-08-01');
        expect(toIsoDate(range.endExclusive)).toBe('2027-08-01');
    });

    it('uses the previous 1 August when now is 31 July', () => {
        const range = getSchoolYearRange(new Date(2026, 6, 31));

        expect(toIsoDate(range.start)).toBe('2025-08-01');
        expect(toIsoDate(range.endExclusive)).toBe('2026-08-01');
    });

    it('supports custom wrapping bounds', () => {
        const range = getSchoolYearRange(
            new Date(2026, 9, 1),
            { month: 9, day: 1 },
            { month: 6, day: 30 },
        );

        expect(toIsoDate(range.start)).toBe('2026-09-01');
        expect(toIsoDate(range.endExclusive)).toBe('2027-07-01');
    });

    it('keeps a non-wrapping range in the same calendar year', () => {
        const range = getSchoolYearRange(
            new Date(2026, 5, 15),
            { month: 1, day: 1 },
            { month: 12, day: 31 },
        );

        expect(toIsoDate(range.start)).toBe('2026-01-01');
        expect(toIsoDate(range.endExclusive)).toBe('2027-01-01');
    });
});

describe('upcoming events', () => {
    const now = new Date('2026-09-01T12:00:00');

    it('treats an event starting now as upcoming', () => {
        expect(isUpcomingEvent({ startsAt: '2026-09-01T12:00:00' }, now)).toBe(true);
        expect(isUpcomingEvent({ startsAt: '2026-09-01T11:59:59' }, now)).toBe(false);
    });

    it('returns the first upcoming event id', () => {
        const events = [
            { id: 1, startsAt: '2026-08-01T10:00:00' },
            { id: 2, startsAt: '2026-09-15T10:00:00' },
            { id: 3, startsAt: '2026-10-01T10:00:00' },
        ];

        expect(findNextEventId(events, now)).toBe(2);
    });

    it('returns null when every event is in the past', () => {
        const events = [
            { id: 1, startsAt: '2026-08-01T10:00:00' },
            { id: 2, startsAt: '2026-08-20T10:00:00' },
        ];

        expect(findNextEventId(events, now)).toBeNull();
    });

    it('returns the first event when the whole list is upcoming', () => {
        const events = [
            { id: 10, startsAt: '2026-09-02T00:00:00' },
            { id: 11, startsAt: '2026-10-01T00:00:00' },
        ];

        expect(findNextEventId(events, now)).toBe(10);
    });
});
