import type { i18n as i18nType } from 'i18next';
import { inject, provide } from 'vue';
import type { TypedTranslation, I18nKey } from '../../../i18n';

const key = Symbol('i18n-translate');

export { I18nKey };

interface Config {
    i18n: i18nType;
}

export function provideTranslation({ i18n }: Config) {
    const t = i18n.t as TypedTranslation;
    provide(key, t);
    return { t };
}

export function injectTranslation() {
    return {
        t: inject(key) as TypedTranslation,
    };
}
