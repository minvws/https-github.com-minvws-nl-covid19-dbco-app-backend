export type Extends<T, U extends T> = U;
export type StringKeys<T> = Extract<keyof T, string>;

export type Sizes = 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl';

import type theme from '../tailwind/theme';

type Theme = typeof theme;

export type ThemeSpacing = keyof Theme['spacing'];
export type ThemeColor = keyof Theme['colors'];
export type ThemeFontFamily = keyof Theme['fontFamily'];
export type ThemeFontSize = keyof Theme['fontSize'];
export type ThemeScreenSize = keyof Theme['screens'];

export type HTMLAnchorElementTarget = '_blank' | '_self' | '_parent' | '_top';

export * from './global';

export { FormRootData, JsonSchema, UiSchema } from './json-forms/types';
