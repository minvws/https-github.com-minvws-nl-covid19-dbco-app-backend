import { inject, provide } from 'vue';
import type { FormActionHandler } from '../../../types';

export const key = Symbol('form-action-handler');

interface Config {
    formActionHandler: FormActionHandler | null;
}

export function provideFormActionHandler({ formActionHandler }: Config) {
    provide(key, formActionHandler);
    return { formActionHandler };
}

export function injectFormActionHandler() {
    return {
        formActionHandler: inject(key) as Config['formActionHandler'],
    };
}
