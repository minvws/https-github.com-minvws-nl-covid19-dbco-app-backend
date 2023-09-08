import type { FormError } from '../../types';

export type JsonFormsEditorChangeEvent<T = any> = {
    data?: T;
    schema?: GenericObject;
    uiSchema?: GenericObject;
    additionalErrors?: FormError[];
};
