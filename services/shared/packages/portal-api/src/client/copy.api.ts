import { getAxiosInstance } from '../defaults';
export const getDiagnostics = (caseUuid: string) =>
    getAxiosInstance()
        .get(`/api/copy/${caseUuid}/diagnostics`)
        .then((res) => res.data);
