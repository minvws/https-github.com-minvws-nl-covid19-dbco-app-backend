import type { i18n as i18nType } from 'i18next';
import type { ErrorTranslator } from '@jsonforms/core';

export function createErrorTranslator({ i18n }: { i18n: i18nType }) {
    /**
     * @see: https://jsonforms.io/docs/i18n#translateerror
     */
    const translateError: ErrorTranslator = (error, translate, uischema) => {
        const { keyword, params } = error;
        const id = `error.${keyword}`;
        if (i18n.exists(id)) {
            return i18n.t(id, params) as string;
        }
        return error.message || '-';
    };

    return translateError;
}
