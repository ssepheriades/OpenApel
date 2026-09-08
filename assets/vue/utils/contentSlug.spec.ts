import { describe, expect, it } from 'vitest';
import { isContentSlug } from './contentSlug';

describe('isContentSlug', () => {
    it('accepts lowercase hyphenated slugs', () => {
        expect(isContentSlug('kermesse-2026')).toBe(true);
        expect(isContentSlug('assemblee-generale')).toBe(true);
    });

    it('rejects empty, numeric-unsafe, or uppercase values', () => {
        expect(isContentSlug('')).toBe(false);
        expect(isContentSlug('a')).toBe(false);
        expect(isContentSlug('Hello')).toBe(false);
        expect(isContentSlug('kermesse_2026')).toBe(false);
        expect(isContentSlug('-kermesse')).toBe(false);
        expect(isContentSlug(12)).toBe(false);
    });
});
