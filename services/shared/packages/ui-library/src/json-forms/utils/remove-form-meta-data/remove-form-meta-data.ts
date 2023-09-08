import { cloneDeep } from 'lodash';
import type { FormData, FormRequestData, FormRootData } from '../../types';

export function removeFormMetaData<T extends FormData | FormRootData>(formData: T | FormRequestData<T> | undefined) {
    if (!formData) return;
    const formDataClone = cloneDeep(formData) as FormRequestData<T>;
    delete formDataClone.$config;
    delete formDataClone.$links;
    delete formDataClone.$validationErrors;
    delete formDataClone.$forms;
    return formDataClone;
}
