import { getAxiosInstance } from '../defaults';
import type { AxiosResponse } from 'axios';

export const getAccessToCase = (uuid: string, token: string) =>
    getAxiosInstance()
        .post('/api/assignment/cases/' + uuid, null, { headers: { 'Assignment-Token': token } })
        .then((res: AxiosResponse) => res.data);
