import { describe, expect, it } from 'vitest';
import { hasAgendaDetail, isSchoolClosureEvent, SCHOOL_CLOSURE_TYPES, type EventType } from './events';

describe('isSchoolClosureEvent', () => {
    it.each(SCHOOL_CLOSURE_TYPES)('treats %s as a school closure', (type) => {
        expect(isSchoolClosureEvent(type)).toBe(true);
    });

    it.each<EventType>(['party', 'public_meeting', 'school_event'])(
        'does not treat %s as a school closure',
        (type) => {
            expect(isSchoolClosureEvent(type)).toBe(false);
        },
    );
});

describe('hasAgendaDetail', () => {
    it.each<EventType>(['vacation', 'public_holiday'])('disables the detail page for %s', (type) => {
        expect(hasAgendaDetail(type)).toBe(false);
    });

    it.each<EventType>(['pedagogical_day', 'party', 'public_meeting', 'school_event'])(
        'keeps the detail page for %s',
        (type) => {
            expect(hasAgendaDetail(type)).toBe(true);
        },
    );
});
