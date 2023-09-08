import type { AxiosResponse, CancelToken } from 'axios';
import type {
    CaseCreateUpdate,
    SearchDTO,
    SearchQueryDTO,
    IndexUpdateContactStatusDTO,
    CaseTimelineDTO,
    UserAssignmentOptionsDTO,
    IndexUpdateContactStatusQueryDTO,
    CaseLockResponse,
    CreateManualTestResultFields,
    PlannerCase,
    UpdatePriority,
    CaseValidationMessages,
    CaseUpdateMeta,
} from '@dbco/portal-api/case.dto';
import type { OsirisLogItem } from '@dbco/portal-api/osiris.dto';
import type { Assignment } from '@dbco/portal-api/assignment';
import type { BcoPhaseV1 } from '@dbco/enum';
import { getAxiosInstance } from '../defaults';
import type { PlannerCaseListItem } from '../caseList.dto';
import { getAssignmentToken } from '../token';

// CRUD
export const createCase = (data: Partial<CaseCreateUpdate>) =>
    getAxiosInstance()
        .post('/api/cases/', data)
        .then((res) => res.data);
export const deleteCase = (uuid: string) => getAxiosInstance().delete(`/api/cases/${uuid}`);
export const getCases = (page = 1, perPage = 30, cancelToken?: CancelToken) =>
    getAxiosInstance()
        .get(`/api/cases/mine`, {
            params: {
                page,
                perPage,
            },
            cancelToken,
        })
        .then((res) => res.data);

export const updateContactStatus = (payload: IndexUpdateContactStatusQueryDTO) => {
    const data: IndexUpdateContactStatusDTO = {
        status_index_contact_tracing: payload.statusIndexContactTracing,
        status_explanation: payload.statusExplanation,
    };

    if (payload.casequalityFeedback !== null) data.casequality_feedback = payload.casequalityFeedback;

    return getAxiosInstance()
        .put(`/api/cases/${payload.uuid}/contact-status`, data)
        .then((res) => res.data);
};

// Meta
export const getMeta = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/case/${uuid}`)
        .then((res) => res.data);

// Status
export const getStatus = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/contact-status`)
        .then((res) => res.data);

// Test Results
export const getTestResults = (caseUuid: string) =>
    getAxiosInstance()
        .get(`/api/cases/${caseUuid}/testresults`)
        .then((res) => res.data);
export const createTestResult = (caseUuid: string, payload: Partial<CreateManualTestResultFields>) =>
    getAxiosInstance().post(`/api/cases/${caseUuid}/testresults`, payload);
export const deleteTestResult = (caseUuid: string, testResultUuid: string) =>
    getAxiosInstance().delete(`/api/cases/${caseUuid}/testresults/${testResultUuid}`);

// Fragments
export const getFragments = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/fragments`)
        .then((res) => res.data);

// Case -- Role: planner
export const getPlannerCase = (uuid: string) =>
    getAxiosInstance()
        .get<{ data: PlannerCase }>(`/api/cases/planner/${uuid}`)
        .then((res) => res.data);
export const updatePlannerCase = (uuid: string, data: Partial<CaseCreateUpdate>) =>
    getAxiosInstance()
        .put(`/api/cases/planner/${uuid}`, data)
        .then((res) => res.data);
export const updatePlannerCaseMeta = (uuid: string, data: Partial<CaseUpdateMeta>) =>
    getAxiosInstance()
        .put(`/api/cases/planner/${uuid}/meta`, data)
        .then((res) => res.data);

// Assignment -- Role: planner
export const assignCase = (uuid: string, userUuid: string) =>
    getAxiosInstance().post('/api/assigncase', {
        caseId: uuid,
        userId: userUuid,
    });
export const getUserAssignmentOptions = () =>
    getAxiosInstance()
        .get<UserAssignmentOptionsDTO>(`/api/cases/assignment/all-user-options`)
        .then((res) => res.data.options);
export const getAssignmentOptions = (uuids: string[]) =>
    getAxiosInstance()
        .post(`/api/cases/${uuids?.length === 1 ? `${uuids}/` : ''}assignment/options`, { cases: uuids })
        .then((res) => res.data);
export const updateAssignment = (uuids: string[], data: Assignment) =>
    getAxiosInstance().put(`/api/cases/${uuids?.length === 1 ? `${uuids}/` : ''}assignment`, data);

export const updateBCOPhase = (phase: BcoPhaseV1, uuids: string[]): Promise<AxiosResponse['data']> => {
    const payload = uuids?.length === 1 ? { bco_phase: phase } : { bco_phase: phase, cases: uuids };
    return getAxiosInstance()
        .put(`/api/cases/${uuids?.length === 1 ? `${uuids}/` : 'multi/'}bcophase`, payload)
        .then((res) => res.data);
};

// Priority -- Role: planner
export const updatePriority = (data: UpdatePriority) => getAxiosInstance().put('/api/cases/priority', data);

// Pairing
export const pair = (uuid: string) =>
    getAxiosInstance()
        .post(`/api/case/${uuid}/pairingcode`, {})
        .then((res) => res.data);

export const reversePair = (uuid: string, code: string) =>
    getAxiosInstance()
        .post(`/api/case/${uuid}/pairingcode`, { code })
        .then((res) => res.data);

// Queue
export const assignNextCaseInQueue = (queue: string) =>
    getAxiosInstance()
        .get(`/api/casequeues/${queue}/next`)
        .then((res) => res.data);

// Search
export const search = (data: SearchQueryDTO) =>
    getAxiosInstance()
        .post<SearchDTO>(`/api/search`, data)
        .then((res) => res.data);

// Planner case search
export const plannerSearch = (identifier: string) =>
    getAxiosInstance()
        .post<PlannerCaseListItem>('/api/cases/planner/search', { identifier })
        .then((res) => res.data);

// Misc - Osiris
export const checkUnansweredQuestions = (uuid: string, version: 'finished' | 'pre-notification') =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/check-unanswered-questions`, { params: { version } })
        .then((res) => res.data);

// Connected cases
export const getConnected = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/connected`)
        .then((res) => res.data);

// History / Timeline
export const getCaseTimeline = (uuid: string) =>
    getAxiosInstance()
        .get<CaseTimelineDTO[]>(`/api/cases/${uuid}/timeline`)
        .then((res) => res.data);

export const getCaseTimelinePlanner = (uuid: string) =>
    getAxiosInstance()
        .get<CaseTimelineDTO[]>(`/api/cases/${uuid}/planner-timeline`)
        .then((res) => res.data);

// Notes
export const addCaseNote = (uuid: string, note: string, type: string, token?: string) =>
    getAxiosInstance()
        .post<void>(`/api/cases/${uuid}/notes `, { note, type }, { headers: getAssignmentToken(token) })
        .then((res) => res.data);

// Misc caseLabels
export const getCaseLabels = () =>
    getAxiosInstance()
        .get('/api/caselabels')
        .then((res) => res.data);

/**
 * Archive case(s)
 *
 * @param uuids Array of case unique identifiers
 * @param note note for archive action
 * @returns Axios (promise) response
 */
export const archiveCases = (uuids: string[], note?: string, sendOsirisNotification?: boolean) =>
    uuids.length === 1
        ? getAxiosInstance()
              .put(`/api/cases/${uuids[0]}/archive`, { note, sendOsirisNotification })
              .then((res) => res.data)
        : getAxiosInstance()
              .put(`/api/cases/archiveMulti`, { cases: uuids, note, sendOsirisNotification })
              .then((res) => res.data);

/**
 * Reopen case(s)
 * @param uuids Array of case unique identifiers
 * @param note note for reopen action
 * @returns Axios (promise) response
 */
export const reopenCases = (uuids: string[], note: string) =>
    uuids.length === 1
        ? getAxiosInstance()
              .patch(`/api/cases/${uuids[0]}/reopen`, { note })
              .then((res) => res.data)
        : getAxiosInstance()
              .patch(`/api/cases/reopenMulti`, { cases: uuids, note })
              .then((res) => res.data);

/**
 * Change case organisation
 * @param caseUuid case unique identifier
 * @param note note for change action
 * @param organisationUuid organisation to change to
 * @returns Axios (promise) response
 */
export const changeOrganisation = (caseUuid: string, note: string, organisationUuid: string) =>
    getAxiosInstance()
        .post<Record<string, unknown>[]>(`/api/case/${caseUuid}/update-organisation`, {
            note,
            organisation_uuid: organisationUuid,
        })
        .then((res) => res.data);

/**
 * Check, refresh and delete case lock from a case
 * @param caseUuid Case unique identifier
 */
export const getCaseLock = (caseUuid: string): Promise<CaseLockResponse> =>
    getAxiosInstance()
        .get<CaseLockResponse['data']>(`/api/case/${caseUuid}/lock`)
        .then((res) => res);
export const refreshCaseLock = (caseUuid: string) =>
    getAxiosInstance()
        .post(`/api/case/${caseUuid}/lock/refresh`)
        .then((res) => res.data);
export const deleteCaseLock = (caseUuid: string) => getAxiosInstance().delete(`/api/case/${caseUuid}/lock/remove`);

// Osiris log
export const getOsirisLog = (caseUuid: string) =>
    getAxiosInstance()
        .get<OsirisLogItem[]>(`/api/cases/${caseUuid}/history/osiris`)
        .then((res) => res.data);

export const getOsirisValidationStatusMessages = (uuid: string): Promise<CaseValidationMessages> => {
    return getAxiosInstance()
        .get<CaseValidationMessages>(`/api/cases/${uuid}/validation-status/messages?filter=tag_osiris_initial`)
        .then((res) => res.data);
};
