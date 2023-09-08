import { getAxiosInstance } from '@dbco/portal-api/defaults';
import type { FormData, FormActionHandler, FormRequestConfig, FormRequestData } from '@dbco/ui-library';
import { removeChildFormData } from '@dbco/ui-library';

export function useFormActionHandler<T extends FormData>() {
    const axios = getAxiosInstance();

    async function sendRequest(config: FormRequestConfig, formData?: FormRequestData<T>) {
        if (!config) throw new Error('RequestConfig was not provided!');
        const { href, method } = config;
        const data = removeChildFormData<T>(formData)?.data;
        const response = await axios<T>(href, { method, data });
        return response.data;
    }

    const formActionHandler: FormActionHandler<T> = {
        read: (config) => sendRequest(config),
        create: (config, data) => sendRequest(config, data),
        update: (config, data) => sendRequest(config, data),
        delete: async (config, data) => {
            await sendRequest(config, data);
        },
    };

    return { formActionHandler };
}
