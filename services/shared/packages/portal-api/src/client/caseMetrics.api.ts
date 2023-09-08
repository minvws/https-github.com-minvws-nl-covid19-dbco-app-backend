import type { CasesCreatedArchivedResponse } from '@dbco/portal-api/caseMetrics.dto';
import type { AxiosRequestConfig } from 'axios';
import { getAxiosInstance } from '../defaults';
export const getList = (eTag: string | null, config: AxiosRequestConfig = {}) => {
    if (eTag) {
        config.headers = {
            'If-None-Match': eTag,
        };
    }
    config.validateStatus = (status) => status === 304 || (status >= 200 && status < 300);

    return getAxiosInstance()
        .get<CasesCreatedArchivedResponse | null>(`/api/cases/metrics/created-archived/`, config)
        .then((res) => (res.status !== 304 ? res.data : null));
};

export const refresh = (config?: AxiosRequestConfig) =>
    getAxiosInstance().post<void>(`/api/cases/metrics/created-archived/refresh`, config);
