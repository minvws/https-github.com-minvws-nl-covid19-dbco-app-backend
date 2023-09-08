import { escape } from 'lodash';

export type SafeHtml = {
    html: string;
};

export const generateSafeHtml = (template: string, values?: Record<string, string>): SafeHtml => {
    // Prevent use of regex if not necessary
    if (!values) return { html: template };

    return { html: template.replace(/\{([^}]+)\}/g, (match, key) => (values[key] ? escape(values[key]) : match)) };
};

// Type narrowing to distinguish between SafeHtml and string
export const isSafeHtml = (text: string | SafeHtml): text is SafeHtml =>
    typeof text === 'object' && (text as SafeHtml).html !== undefined;
