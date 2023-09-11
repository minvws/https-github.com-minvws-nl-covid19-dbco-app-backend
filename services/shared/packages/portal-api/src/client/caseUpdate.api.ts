import type { CaseUpdateItem, CaseUpdateRequest, CaseUpdatesResponse } from '@dbco/portal-api/caseUpdate.dto';
import { getAxiosInstance } from '../defaults';
export const listCaseUpdates = (caseUuid: string): Promise<CaseUpdatesResponse> =>
    getAxiosInstance()
        .get(`/api/cases/${caseUuid}/updates`)
        .then((res) => res.data);

export const getCaseUpdate = (caseUuid: string, updateUuid: string): Promise<CaseUpdateItem> =>
    getAxiosInstance()
        .get(`/api/cases/${caseUuid}/updates/${updateUuid}`)
        .then((res) => res.data);

export const applyCaseUpdate = (
    caseUuid: string,
    updateUuid: string,
    data: CaseUpdateRequest
): Promise<CaseUpdateItem> =>
    getAxiosInstance()
        .post(`/api/cases/${caseUuid}/updates/${updateUuid}/apply`, data)
        .then((res) => res.data);
