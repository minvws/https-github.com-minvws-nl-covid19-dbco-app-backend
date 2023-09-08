import type { AxiosInstance, AxiosRequestConfig } from 'axios';
import axios from 'axios';
import { isFunction } from 'lodash';
let instance: AxiosInstance | undefined;

export function createAxiosInstance(options: AxiosRequestConfig, setup?: (instance: AxiosInstance) => AxiosInstance) {
    instance = isFunction(setup) ? setup(axios.create(options)) : axios.create(options);
}

export function getAxiosInstance() {
    if (!instance) {
        throw Error('No axios instance initialized');
    }

    return instance;
}
