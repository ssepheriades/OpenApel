import { describe, expect, it } from 'vitest';
import { excerptFromMarkdown, renderMarkdown } from './markdown';

describe('renderMarkdown', () => {
    it('keeps links, emphasis and lists', () => {
        const html = renderMarkdown('[APEL](https://example.org) et **nos enfants**\n\n- un\n- deux');

        expect(html).toContain('<a href="https://example.org">APEL</a>');
        expect(html).toContain('<strong>nos enfants</strong>');
        expect(html).toContain('<li>un</li>');
        expect(html).toContain('<li>deux</li>');
    });

    it('strips raw HTML and scripts', () => {
        const html = renderMarkdown('Hello <script>alert(1)</script> <img src=x onerror=alert(1)> world');

        expect(html).not.toMatch(/<script[\s>]/i);
        expect(html).not.toMatch(/<img[\s>]/i);
        expect(html).toContain('Hello');
        expect(html).toContain('world');
    });
});

describe('excerptFromMarkdown', () => {
    it('strips markdown syntax and keeps link labels', () => {
        const excerpt = excerptFromMarkdown('## Titre\n\nLisez [ceci](https://example.org) et **cela**.');

        expect(excerpt).toBe('Titre Lisez ceci et cela.');
    });

    it('truncates long text on a word boundary', () => {
        const source = 'Les familles de l’école se retrouvent chaque trimestre autour d’un goûter partagé.';
        const excerpt = excerptFromMarkdown(source, 40);

        expect(excerpt.endsWith('…')).toBe(true);
        expect(excerpt.length).toBeLessThanOrEqual(41);
        expect(excerpt).not.toContain('goûter');
    });

    it('returns short text unchanged', () => {
        expect(excerptFromMarkdown('Nouvelle courte.')).toBe('Nouvelle courte.');
    });
});
