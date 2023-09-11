import type { CallcenterSearchRequest } from '@dbco/portal-api/callcenter.dto';
import { getAxiosInstance } from '../defaults';
import type { AxiosResponse } from 'axios';

export const search = (payload: CallcenterSearchRequest) =>
    getAxiosInstance()
        .post('/api/callcenter/search', payload)
        .then((res: AxiosResponse) => res.data);
