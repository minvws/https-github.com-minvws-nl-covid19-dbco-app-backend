import type { FormActionHandler, FormData, FormRequestConfig, FormRequestData } from '../../types';
import { removeChildFormData, removeFormMetaData } from '../../utils';
import { compose } from 'lodash/fp';

function indentString(value: string, count = 2, indent = ' ') {
    if (!value) return '';
    return value.replace(/^/gm, indent.repeat(count));
}

function fakeRequest<T extends FormData = FormData>(
    action: keyof FormActionHandler,
    { href, method }: FormRequestConfig,
    formData?: FormRequestData<T>
) {
    const prepareRequestData = compose(removeFormMetaData<T>, removeChildFormData<T>);
    const requestData = prepareRequestData(formData);

    return new Promise<T>((resolve) => {
        let argString = '';
        argString += `\nhref: ${href}`;
        argString += `\nmethod: ${method}`;
        if (requestData) {
            argString += `\ndata:\n${indentString(JSON.stringify(requestData, null, 2))}`;
        }
        console.warn(`Debug Action Handler :: ${action} :: ${indentString(argString)}`);
        setTimeout(resolve, 400);
    });
}

type Config<T extends FormData> = {
    defaultData: T;
};

export function useDebugFormActionHandler<T extends FormData = FormData>({ defaultData }: Config<T>) {
    const debugFormActionHandler: FormActionHandler<T> = {
        create: async (link, data) => {
            await fakeRequest('create', link, data);
            return data as T;
        },
        read: async (link) => {
            await fakeRequest('read', link);
            return defaultData;
        },
        update: async (link, data) => {
            await fakeRequest('update', link, data);
            return data as T;
        },
        delete: async (link, data) => {
            await fakeRequest('delete', link, data);
        },
    };

    return { debugFormActionHandler };
}
