/* eslint-disable @typescript-eslint/no-explicit-any */
import type { Translator } from '@jsonforms/core';
import type { FormError, JsonSchema, UiSchema } from '../types';
import type { i18n } from 'i18next';

type Context = {
    errors?: FormError[];
    path?: string;
    schema?: JsonSchema;
    uiSchema?: UiSchema;
    [key: string]: any;
};

export function createTranslator({ i18n }: { i18n: i18n }) {
    /**
     * @see: https://jsonforms.io/docs/i18n#translate
     */
    function translator(id: string, defaultMessage: string, values?: Context): string;
    function translator(id: string, defaultMessage: undefined, values?: Context): string | undefined;
    function translator(id: string, defaultMessage: any, values?: Context): any {
        if (i18n.exists(id)) {
            return i18n.t(id);
        }
        return defaultMessage;
    }

    return translator as Translator;
}
