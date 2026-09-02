export interface MonthDay {
    month: number;
    day: number;
}

export interface SchoolYearRange {
    start: Date;
    endExclusive: Date;
}

export const DEFAULT_SCHOOL_YEAR_START: MonthDay = { month: 8, day: 1 };
export const DEFAULT_SCHOOL_YEAR_END: MonthDay = { month: 7, day: 31 };

export function parseMonthDay(
    isoDate: string | null | undefined,
    fallback: MonthDay = DEFAULT_SCHOOL_YEAR_START,
): MonthDay {
    if (!isoDate) {
        return fallback;
    }

    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(isoDate);
    if (!match) {
        return fallback;
    }

    const month = Number(match[2]);
    const day = Number(match[3]);
    if (month < 1 || month > 12 || day < 1 || day > 31) {
        return fallback;
    }

    return { month, day };
}

export function toIsoDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function getSchoolYearRange(
    now: Date,
    startMd: MonthDay = DEFAULT_SCHOOL_YEAR_START,
    endMd: MonthDay = DEFAULT_SCHOOL_YEAR_END,
): SchoolYearRange {
    const year = now.getFullYear();
    const nowValue = monthDayValue({ month: now.getMonth() + 1, day: now.getDate() });
    const startValue = monthDayValue(startMd);
    const wrapsCalendarYear = startValue > monthDayValue(endMd);

    if (!wrapsCalendarYear) {
        return {
            start: localDate(year, startMd),
            endExclusive: addOneDay(localDate(year, endMd)),
        };
    }

    if (nowValue >= startValue) {
        return {
            start: localDate(year, startMd),
            endExclusive: addOneDay(localDate(year + 1, endMd)),
        };
    }

    return {
        start: localDate(year - 1, startMd),
        endExclusive: addOneDay(localDate(year, endMd)),
    };
}

export function isUpcomingEvent(event: { startsAt: string }, now: Date = new Date()): boolean {
    return new Date(event.startsAt) >= now;
}

export function findNextEventId(
    events: Array<{ id: number; startsAt: string }>,
    now: Date = new Date(),
): number | null {
    return events.find((event) => isUpcomingEvent(event, now))?.id ?? null;
}

function monthDayValue(monthDay: MonthDay): number {
    return monthDay.month * 100 + monthDay.day;
}

function localDate(year: number, monthDay: MonthDay): Date {
    return new Date(year, monthDay.month - 1, monthDay.day);
}

function addOneDay(date: Date): Date {
    const next = new Date(date);
    next.setDate(next.getDate() + 1);

    return next;
}
