import Vue from 'vue';
import VueI18n from 'vue-i18n';
import { Locales } from './locales';
import { createI18n } from 'vue-i18n-composable';

//import en from './languages/en.json';
import nl from './languages/nl.json';

const defaultLocale = Locales.NL;

export const translationFiles = {
    // [Locales.EN]: en,
    [Locales.NL]: nl,
};

Vue.use(VueI18n);

export const i18n = createI18n({
    messages: translationFiles,
    locale: defaultLocale,
    fallbackLocale: defaultLocale,
});

export default i18n;
