import { vi, TranslationKey } from './vi';

export type Locale = 'vi';

export const DEFAULT_LOCALE: Locale = 'vi';

const dictionaries: Record<Locale, Record<string, string>> = {
    vi,
};

/**
 * Hệ thống i18n tập trung cho toàn bộ giao diện React.
 * Ngôn ngữ mặc định là tiếng Việt (vi).
 */
export function t(
    key: TranslationKey | string,
    replacements?: Record<string, string | number>,
): string {
    const dict = dictionaries[DEFAULT_LOCALE];
    let value = dict[key] ?? String(key);

    if (replacements) {
        for (const [name, replacement] of Object.entries(replacements)) {
            value = value.split(`:${name}`).join(String(replacement));
        }
    }

    return value;
}

export const locale: Locale = DEFAULT_LOCALE;
