/** Keep in sync with App\Service\ContentSlugger */
export const CONTENT_SLUG_PATTERN = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
export const CONTENT_SLUG_MAX_LENGTH = 80;
export const CONTENT_SLUG_MIN_LENGTH = 2;

export function isContentSlug(value: unknown): value is string {
    return (
        typeof value === 'string'
        && value.length >= CONTENT_SLUG_MIN_LENGTH
        && value.length <= CONTENT_SLUG_MAX_LENGTH
        && CONTENT_SLUG_PATTERN.test(value)
    );
}
