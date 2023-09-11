import type { Context } from '../context.dto';
import { getAxiosInstance } from '../defaults';
import type { ValidationResult } from '../validation-result.dto';
// CRUD
export const createContext = (
    uuid: string,
    data: Context
): Promise<{ context: Context; validationResult?: ValidationResult }> =>
    getAxiosInstance()
        .post<{ context: Context; errors: Record<string, string> }>(`/api/cases/${uuid}/contexts`, { context: data })
        .then((res) => res.data);
export const deleteContext = (uuid: string) => getAxiosInstance().delete(`/api/contexts/${uuid}`);
export const getContexts = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/contexts`)
        .then((res) => res.data);
export const getContextsForOrganisation = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/contexts`)
        .then((res) => res.data);
export const updateContext = (
    uuid: string,
    data: Context
): Promise<{ context: Context; validationResult?: ValidationResult }> =>
    getAxiosInstance()
        .put(`/api/contexts/${uuid}`, { context: data })
        .then((res) => res.data);

// Fragments
export const getFragments = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/contexts/${uuid}/fragments`)
        .then((res) => res.data);

// Sections
export const getSections = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/contexts/${uuid}/sections`)
        .then((res) => res.data);
export const linkSection = (uuid: string, sectionUuid: string) =>
    getAxiosInstance().post(`/api/contexts/${uuid}/sections/${sectionUuid}`);
export const unlinkSection = (uuid: string, sectionUuid: string) =>
    getAxiosInstance().delete(`/api/contexts/${uuid}/sections/${sectionUuid}`);

// Places
export const linkPlace = (contextUuid: string, placeUuid: string) =>
    getAxiosInstance().post(`/api/contexts/${contextUuid}/place/${placeUuid}`);
export const unlinkPlace = (contextUuid: string, placeUuid: string) =>
    getAxiosInstance().delete(`/api/contexts/${contextUuid}/place/${placeUuid}`);
