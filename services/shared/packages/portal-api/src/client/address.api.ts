import type { CancelToken } from 'axios';
import { getAxiosInstance } from '../defaults';

export const search = (postalCode: string, houseNumber: string, cancelToken?: CancelToken) =>
    getAxiosInstance()
        .get(`/api/addresses`, { params: { postalCode, houseNumber }, cancelToken })
        .then((res) => res.data);
