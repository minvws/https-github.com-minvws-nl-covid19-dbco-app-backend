import type { FormError } from './json-forms';

export interface FormChangeEvent<T = any> {
    data: T;
    errors: FormError[];
}

export interface ChildFormChangeEvent<T = any> extends FormChangeEvent<T> {
    path: string;
}

export type FormLinkEvent = { href: string };
