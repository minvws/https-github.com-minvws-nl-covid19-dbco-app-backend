import type { AxiosResponse, CancelToken } from 'axios';
import type {
    CallToActionHistoryItems,
    CallToActionRequest,
    CallToActionResponse,
    CallToActionSortOptions,
} from '@dbco/portal-api/callToAction.dto';
import type { PaginatedRequestOptions, PaginatedResponse } from '@dbco/portal-api/pagination';
import { getAxiosInstance } from '../defaults';
import { getAssignmentToken } from '../token';

export const assignToUser = async (uuid: string): Promise<CallToActionResponse> => {
    const { data }: AxiosResponse<CallToActionResponse> = await getAxiosInstance().post(
        `api/call-to-actions/${uuid}/pickup`
    );
    return data;
};

export const complete = async (uuid: string, note: string): Promise<CallToActionResponse> => {
    const { data }: AxiosResponse<CallToActionResponse> = await getAxiosInstance().post(
        `api/call-to-actions/${uuid}/complete`,
        {
            note,
        }
    );
    return data;
};

export const deleteAssignment = async (uuid: string, note: string): Promise<CallToActionResponse> => {
    const { data }: AxiosResponse<CallToActionResponse> = await getAxiosInstance().post(
        `api/call-to-actions/${uuid}/drop`,
        {
            note,
        }
    );
    return data;
};

export const getAll = async (
    options: PaginatedRequestOptions<CallToActionSortOptions>,
    cancelToken?: CancelToken
): Promise<PaginatedResponse<CallToActionResponse>> => {
    const { page, perPage, order, sort } = options;
    const { data }: AxiosResponse<PaginatedResponse<CallToActionResponse>> = await getAxiosInstance().get(
        `/api/call-to-actions`,
        {
            params: {
                page,
                perPage,
                order,
                sort,
            },
            cancelToken,
        }
    );
    return data;
};

export const get = async (uuid: string): Promise<CallToActionResponse> => {
    const { data }: AxiosResponse<CallToActionResponse> = await getAxiosInstance().get(`/api/call-to-actions/${uuid}`);
    return data;
};

export const getCaseHistory = async (uuid: string): Promise<CallToActionHistoryItems> => {
    const { data }: AxiosResponse<CallToActionHistoryItems> = await getAxiosInstance().get(
        `/api/call-to-actions/${uuid}/history`
    );
    return data;
};

export const create = async (payload: CallToActionRequest, token?: string): Promise<CallToActionResponse> => {
    const { data }: AxiosResponse<CallToActionResponse> = await getAxiosInstance().put(
        `/api/call-to-actions/`,
        payload,
        {
            headers: getAssignmentToken(token),
        }
    );
    return data;
};
