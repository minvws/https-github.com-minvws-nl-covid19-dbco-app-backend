import type { ResourceLanguage } from 'i18next';
import type { FormCollectionData, FormData, FormError, JsonSchema, UiSchema } from '../../types';

export type JsonFormsStoryProps<T extends GenericObject = GenericObject> = {
    data: FormData | FormCollectionData | T;
    schema: JsonSchema;
    uiSchema: UiSchema;
    additionalErrors?: FormError[];
    useActionHandler?: boolean;
    i18nResource?: ResourceLanguage;
};
