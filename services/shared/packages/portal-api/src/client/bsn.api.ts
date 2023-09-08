import type { BsnLookupRequest, BsnLookupResponse } from '@dbco/portal-api/bsn.dto';
import { getAxiosInstance } from '../defaults';

export const bsnLookup = (data: BsnLookupRequest): Promise<BsnLookupResponse> =>
    getAxiosInstance()
        .post('/api/pseudo-bsn/lookup', data)
        .then((res) => res.data);
export const updateIndexBsn = (uuid: string, pseudoBsnGuid: string) =>
    getAxiosInstance()
        .put(`/api/cases/${uuid}/pseudo-bsn`, {
            pseudoBsnGuid: pseudoBsnGuid,
        })
        .then((res) => res.data);
export const updateTaskBsn = (uuid: string, pseudoBsnGuid: string) =>
    getAxiosInstance()
        .put(`/api/tasks/${uuid}/pseudo-bsn`, {
            pseudoBsnGuid: pseudoBsnGuid,
        })
        .then((res) => res.data);
