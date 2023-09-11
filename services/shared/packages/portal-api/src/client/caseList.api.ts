import type { CancelToken } from 'axios';
import type { CaseList, CaseListWithStats, PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import { getAxiosInstance } from '../defaults';
import type { PaginatedResponse } from '@dbco/portal-api/pagination';

export enum CaseListStatsMode {
    Disabled = 0,
    Enabled = 1,
}
// CRUD
export const getList = (listUuid: string) =>
    getAxiosInstance()
        .get<CaseList>(`/api/caselists/${listUuid}`)
        .then((res) => res.data);
export const createList = (name: string) =>
    getAxiosInstance()
        .post('/api/caselists', { name })
        .then((res) => res.data);
export const deleteList = (listUuid: string, force = false) =>
    getAxiosInstance().delete(`/api/caselists/${listUuid}`, {
        params: {
            force: force ? 1 : 0,
        },
    });

export const getLists = (stats: CaseListStatsMode, types: string, page = 1, cancelToken?: CancelToken) =>
    getAxiosInstance()
        .get<PaginatedResponse<CaseListWithStats | CaseList>>('/api/caselists', {
            params: {
                stats,
                types,
                page,
            },
            cancelToken,
        })
        .then(({ data }) => data);

export const getListCases = (
    list: string | undefined,
    listFilter: ListFilterOptions,
    filterParams: Record<string, string | Record<string, number> | null>,
    page = 1,
    perPage = 30,
    sort?: ListSortOptions,
    order?: 'asc' | 'desc',
    cancelToken?: CancelToken
) =>
    getAxiosInstance()
        .get<PaginatedResponse<PlannerCaseListItem>>(`/api/${list ? `caselists/${list}/` : ''}cases/${listFilter}`, {
            params: {
                page,
                perPage,
                sort,
                order,
                ...filterParams,
            },
            cancelToken,
        })
        .then((res) => res.data);

export const updateList = (listUuid: string, name: string) =>
    getAxiosInstance()
        .put(`/api/caselists/${listUuid}`, { name })
        .then((res) => res.data);

// Count
export const listCounts = (list: string | null) =>
    getAxiosInstance()
        .get(`/api/${list ? `caselists/${list}/` : ''}cases/counts`)
        .then((res) => res.data);

// Intake
export const getIntakeCases = (
    page = 1,
    perPage = 30,
    filter?: Record<string, string>,
    sort?: ListSortOptions,
    order?: 'asc' | 'desc',
    cancelToken?: CancelToken
) =>
    getAxiosInstance()
        .get('/api/intakes', {
            params: {
                page,
                perPage,
                filter,
                sort,
                order,
            },
            cancelToken,
        })
        .then((res) => res.data);
export const intakeCount = () =>
    getAxiosInstance()
        .get('/api/intakes/count')
        .then((res) => res.data);

// Enums
export enum ListFilterOptions {
    Unassigned = 'unassigned',
    Queued = 'queued',
    Outsourced = 'outsourced',
    Assigned = 'assigned',
    Completed = 'completed',
    Archived = 'archived',
}

export enum ListSortOptions {
    CreatedAt = 'createdAt',
    UpdatedAt = 'updatedAt',
    DateOfSymptomOnset = 'dateOfSymptomOnset',
    DateOfTest = 'dateOfTest',
    Priority = 'priority',
    ContactsCount = 'contactsCount',
    Status = 'status',
    Cat1Count = 'cat1Count',
    EstimatedCat2Count = 'estimatedCat2Count',
}
