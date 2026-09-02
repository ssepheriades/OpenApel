import DOMPurify from 'dompurify';
import MarkdownIt from 'markdown-it';

const markdown = new MarkdownIt({
    html: false,
    linkify: true,
    typographer: true,
});

const ALLOWED_TAGS = [
    'p',
    'br',
    'strong',
    'em',
    'a',
    'ul',
    'ol',
    'li',
    'h1',
    'h2',
    'h3',
    'h4',
    'blockquote',
    'code',
    'pre',
    'hr',
];

/**
 * Renders Markdown to sanitized HTML for the SPA. HTML in the source is ignored;
 * only a tight tag whitelist survives DOMPurify.
 */
export function renderMarkdown(source: string): string {
    return DOMPurify.sanitize(markdown.render(source), {
        ALLOWED_TAGS,
        ALLOWED_ATTR: ['href', 'title'],
    });
}

/**
 * Plain-text excerpt from Markdown for card previews. Strips syntax rather than
 * rendering HTML so listings stay free of leftover tags.
 */
export function excerptFromMarkdown(source: string, maxLength = 160): string {
    const plain = source
        .replace(/```[\s\S]*?```/g, ' ')
        .replace(/`([^`]+)`/g, '$1')
        .replace(/!\[[^\]]*]\([^)]+\)/g, ' ')
        .replace(/\[([^\]]+)]\([^)]+\)/g, '$1')
        .replace(/^#{1,6}\s+/gm, '')
        .replace(/[*_~]+/g, '')
        .replace(/^\s*[-*+]\s+/gm, '')
        .replace(/^\s*>\s+/gm, '')
        .replace(/^\s*\d+\.\s+/gm, '')
        .replace(/\s+/g, ' ')
        .trim();

    if (plain.length <= maxLength) {
        return plain;
    }

    const sliced = plain.slice(0, maxLength);
    const lastSpace = sliced.lastIndexOf(' ');
    const cut = lastSpace > maxLength * 0.6 ? sliced.slice(0, lastSpace) : sliced;

    return `${cut.trimEnd()}…`;
}
