import type { AxiosError, AxiosRequestConfig, AxiosResponse, InternalAxiosRequestConfig } from 'axios';
import { userCanEdit } from '@/utils/interfaceState';
import { useAppStore } from '@/store/app/appStore';
import axios from 'axios';

export const lastUpdatedRequestInterceptor = (config: InternalAxiosRequestConfig) => {
    if (!shouldAllowRequest(config)) return config;

    if (shouldHandleRequest(config)) {
        useAppStore().handleRequestStart();
    }

    return config;
};

export const lastUpdatedResponseInterceptor = (response: AxiosResponse) => {
    if (shouldHandleRequest(response.config)) {
        useAppStore().handleRequestComplete(true);
    }

    return response;
};

export const lastUpdatedErrorInterceptor = (error: AxiosError | Error) => {
    if ((axios.isAxiosError(error) && shouldHandleRequest(error.config)) || axios.isCancel(error)) {
        useAppStore().handleRequestComplete(false);
    }

    return Promise.reject(error);
};

function shouldAllowRequest(config: AxiosRequestConfig) {
    if (!config?.method) return false;

    const alwaysAllowed = ['get', 'options'];
    return alwaysAllowed.includes(config.method?.toLowerCase()) || userCanEdit();
}

function shouldHandleRequest(config?: AxiosRequestConfig) {
    if (!config?.method) return false;

    if (shouldIgnoreUrl(config.url)) return false;

    const methodsToHandle = ['delete', 'patch', 'post', 'put'];
    return methodsToHandle.includes(config.method.toLowerCase());
}

function shouldIgnoreUrl(url: string | undefined) {
    const ignoreUrlEndings = ['/session-refresh', '/lock/refresh'];
    return ignoreUrlEndings.some((ending) => url?.endsWith(ending));
}
