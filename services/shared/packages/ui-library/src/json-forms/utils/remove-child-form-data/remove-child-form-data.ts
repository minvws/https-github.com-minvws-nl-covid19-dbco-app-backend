import { isPlainObject } from 'lodash';
import type { FormData, FormRequestData } from '../../types';
import { isFormData } from '../type-guards/type-guards';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function stripFormData(data: any) {
    if (!isPlainObject(data)) return data;
    const filteredData: any = {}; // eslint-disable-line @typescript-eslint/no-explicit-any
    Object.entries(data).forEach(([key, value]) => {
        if (isFormData(value)) return;
        filteredData[key] = stripFormData(value);
    });
    return filteredData;
}

export function removeChildFormData<T extends FormData>(formData: T | FormRequestData<T> | undefined) {
    if (!formData) return;
    return stripFormData(formData);
}
