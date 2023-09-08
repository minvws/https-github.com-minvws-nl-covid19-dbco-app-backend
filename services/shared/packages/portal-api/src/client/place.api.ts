import type { AxiosResponse, CancelToken } from 'axios';
import type { ContextCategoryGroupV1 } from '@dbco/enum';
import { getAxiosInstance } from '../defaults';
import type {
    PlaceDTO,
    PlaceCasesResponse,
    PlaceCasesSortOptions,
    PlaceListResponse,
    PlaceSortOptions,
} from '@dbco/portal-api/place.dto';
import type { PaginatedRequestOptions, PaginatedResponse } from '@dbco/portal-api/pagination';
import type { Section } from '../section.dto';

// CRUD
export const createPlace = (data: Partial<PlaceDTO>) =>
    getAxiosInstance()
        .post('/api/places', data)
        .then((res) => res.data);
export const updatePlace = (data: Partial<PlaceDTO>) =>
    getAxiosInstance()
        .put(`/api/places/${data.uuid}`, data)
        .then((res) => res.data);
export const getPlace = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/places/${uuid}`)
        .then((res) => res.data);

// CLUSTER
export const resetIndexCount = (placeUuid: string) =>
    getAxiosInstance()
        .post(`/api/places/${placeUuid}/cluster/reset`)
        .then((res) => res.data);

export const getCases = async (
    options: PaginatedRequestOptions<PlaceCasesSortOptions>,
    placeUuid: string,
    cancelToken?: CancelToken
): Promise<PaginatedResponse<PlaceCasesResponse>> => {
    const { page, perPage, order, sort } = options;
    const { data }: AxiosResponse<PaginatedResponse<PlaceCasesResponse>> = await getAxiosInstance().get(
        `/api/places/${placeUuid}/cases`,
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

// Search
export const search = (query: string) =>
    getAxiosInstance()
        .post(`/api/places/search`, { query })
        .then((res) => res.data);

/**
 * Endpoint for searching places in context management
 * @param query
 * @returns
 */
export const searchSimilar = (query: string) =>
    getAxiosInstance()
        .post(`/api/places/search/similar`, { query })
        .then((res) => res.data);
// Verify
export const verify = (uuid: string): Promise<PlaceDTO> =>
    getAxiosInstance()
        .put(`/api/places/${uuid}/verify`)
        .then((res) => res.data);
export const unverify = (uuid: string): Promise<PlaceDTO> =>
    getAxiosInstance()
        .put(`/api/places/${uuid}/unverify`)
        .then((res) => res.data);
export const verifyMulti = (placeUuids: string[]) =>
    getAxiosInstance()
        .put(`/api/places/verifyMulti`, { placeUuids })
        .then((res) => res.data);

export enum VerifiedFilter {
    All = 'ALL',
    Verified = 'true',
    Unverified = 'false',
}
// ListType
export const getPlacesByListType = (
    page: number,
    listType?: string,
    query?: string,
    isVerified: VerifiedFilter = VerifiedFilter.All,
    categoryGroup: ContextCategoryGroupV1 | 'all' = 'all',
    sort?: PlaceSortOptions,
    order?: PaginatedRequestOptions['order'],
    perPage = 30
): Promise<PlaceListResponse> =>
    getAxiosInstance()
        .get(`/api/places/search/similar`, {
            params: {
                query,
                page,
                perPage,
                sort,
                order,
                view: listType,
                ...(isVerified !== VerifiedFilter.All ? { isVerified } : {}),
                categoryGroup: categoryGroup !== 'all' ? categoryGroup : undefined,
            },
        })
        .then((res) => res.data);

//Merge
export const merge = (targetUuid: string, places: string[]) =>
    getAxiosInstance().put(`/api/places/${targetUuid}/merge`, { merge_places: places });

// Sections
export const createPlaceSections = (uuid: string, sections: Array<Partial<Section>>, context_uuid?: string) =>
    getAxiosInstance()
        .put(`/api/places/${uuid}/sections`, { context_uuid, sections })
        .then((res) => res.data);

export const updatePlaceSections = (uuid: string, sections: Array<Partial<Section>>, context_uuid?: string) =>
    getAxiosInstance()
        .patch(`/api/places/${uuid}/sections`, { context_uuid, sections })
        .then((res) => res.data);

export const getSections = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/places/${uuid}/sections`)
        .then((res) => res.data);

export const mergeSections = (placeUuid: string, targetUuid: string, sectionUuids: string[]) =>
    getAxiosInstance().post(`/api/places/${placeUuid}/sections/${targetUuid}/merge`, { merge_sections: sectionUuids });
