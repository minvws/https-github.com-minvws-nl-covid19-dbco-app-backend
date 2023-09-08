import type { RawAxiosRequestHeaders } from 'axios';

export const getAssignmentToken = (token?: string): RawAxiosRequestHeaders => {
    return token ? { 'Assignment-Token': token } : {};
};
