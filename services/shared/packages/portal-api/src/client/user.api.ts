import { getAxiosInstance } from '../defaults';
export const getAssignableUsers = () =>
    getAxiosInstance()
        .post(`/api/users/assignable`)
        .then((res) => res.data);
export const updateOrganisation = (payload: { isAvailableForOutsourcing?: boolean; bcoPhase?: string }) =>
    getAxiosInstance()
        .put('/api/organisations/current', payload)
        .then((res) => res.data);
