import { getAxiosInstance } from '../defaults';
import type {
    CatalogCategory,
    CatalogDetailResponse,
    CatalogListResponse,
    Filters,
} from '@dbco/portal-api/catalog.dto';

export const index = (
    purpose: string | null,
    categories?: CatalogCategory[],
    filters?: Filters
): Promise<CatalogListResponse> => {
    const params: any = {};
    if (categories && categories.length > 0) {
        params['categories'] = categories.join(',');
    }

    if (purpose !== '') {
        params['purpose'] = purpose;
    }

    if (filters) {
        params['filter'] = filters;
    }

    return getAxiosInstance()
        .get<CatalogListResponse>(`/api/catalog`, { params })
        .then((res) => res.data);
};

export const show = (
    className: string,
    version: number,
    purpose: string | null,
    diffToVersion?: number
): Promise<CatalogDetailResponse> => {
    const url = `/api/catalog/${encodeURIComponent(className)}/${version}`;

    const params: any = {
        params: {
            diffToVersion,
            purpose,
        },
    };

    if (purpose === '') {
        delete params.params.purpose;
    }

    return getAxiosInstance()
        .get<CatalogDetailResponse>(url, params)
        .then((res) => res.data);
};
