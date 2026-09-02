/**
 * Minimal shape of the Vuetify instance we mutate: keeps the store testable
 * with a plain object instead of a full createVuetify() in jsdom.
 */
export interface BrandThemeTarget {
    theme: {
        themes: {
            value: Record<string, { colors: Record<string, string> }>;
        };
    };
}

/**
 * Vuetify 3 regenerates its CSS variables when the theme definition is mutated,
 * so overriding the colors at runtime is enough to re-skin the whole app.
 */
export function applyBrandColors(target: BrandThemeTarget, primary: string, secondary: string): void {
    const colors = target.theme.themes.value.light?.colors;

    if (!colors) {
        return;
    }

    colors.primary = primary;
    colors.secondary = secondary;
    colors.accent = secondary;
}
