import type { i18n as i18nType, ResourceLanguage } from 'i18next';
import i18next from 'i18next';
import type { JsonFormsI18nState } from '@jsonforms/core';
import { createTranslator } from './translate';
import { createErrorTranslator } from './translateError';
import { nl } from './locales/nl';
import { defaultsDeep } from 'lodash';
import type { FlattenedKeys, ReplaceFirstArg } from '../../types/helpers';

interface i18nConfig {
    resource?: ResourceLanguage;
}

export type I18nKey = FlattenedKeys<typeof nl.translation>;
export type TypedTranslation = ReplaceFirstArg<i18nType['t'], I18nKey>;

export function createI18n({ resource }: i18nConfig = {}) {
    const i18n = i18next.createInstance();
    const nlResource = { translation: defaultsDeep(resource || {}, nl.translation) };

    void i18n.init({
        lng: 'nl',
        debug: false,
        resources: {
            nl: nlResource,
        },
    });

    const i18nState: JsonFormsI18nState = {
        locale: i18n.language,
        translate: createTranslator({ i18n }),
        translateError: createErrorTranslator({ i18n }),
    };

    return {
        i18n,
        i18nState,
    };
}
