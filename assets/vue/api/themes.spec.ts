import { describe, expect, it } from 'vitest';
import { CONTENT_THEMES, THEME_ICONS, THEME_LABELS } from '@/api/themes';

describe('content themes', () => {
    it('exposes a French label and MDI icon for every theme', () => {
        expect(CONTENT_THEMES).toEqual([
            'apprentissage',
            'sport',
            'culture',
            'alimentation',
            'extra_scolaire',
            'autre',
        ]);

        for (const theme of CONTENT_THEMES) {
            expect(THEME_LABELS[theme].length).toBeGreaterThan(0);
            expect(THEME_ICONS[theme]).toMatch(/^mdi-/);
        }
    });
});
