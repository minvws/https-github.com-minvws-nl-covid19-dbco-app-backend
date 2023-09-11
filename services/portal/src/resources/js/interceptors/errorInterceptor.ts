import type { AxiosError } from 'axios';
import ignoreList from './errorInterceptor.ignoreList';
import { useAppStore } from '@/store/app/appStore';

export const errorInterceptor = (error: AxiosError) => {
    const appStore = useAppStore();
    const statusCode = error.response?.status || -1;
    const requestUrl = error.response?.config?.url || '';

    // If there is an 4xx or 5xx error, excluding the following:
    //  404 Not Found
    //  409 Conflict
    //  410 Gone
    //  422 Unprocessable Entity
    // AND the request URL is not in the ignore list
    const isError = statusCode >= 400 && ![404, 409, 410, 422].includes(statusCode);
    if (isError && !ignoreList.includes(requestUrl)) {
        statusCode === 403 ? appStore.setHasPermissionError(true) : appStore.setHasError(true);
    }

    return Promise.reject(error);
};
