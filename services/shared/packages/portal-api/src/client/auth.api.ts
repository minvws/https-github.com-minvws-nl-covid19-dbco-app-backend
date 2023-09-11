import { getAxiosInstance } from '../defaults';

export const refreshSession = () => getAxiosInstance().post(`/api/session-refresh`);
