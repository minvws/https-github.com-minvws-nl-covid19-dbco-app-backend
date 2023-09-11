import { getAxiosInstance } from '../defaults';
import type { Task } from '../task.dto';
// CRUD
export const createTask = (uuid: string, data: Partial<Task>) =>
    getAxiosInstance()
        .post(`/api/cases/${uuid}/tasks`, { task: data })
        .then((res) => res.data);
export const deleteTask = (uuid: string) => getAxiosInstance().delete(`/api/tasks/${uuid}`);
export const getTasks = (uuid: string, type: string, includeProgress?: boolean) =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/tasks/${type}`, {
            params: { includeProgress },
        })
        .then((res) => res.data);
export const updateTask = (uuid: string, data: Partial<Task>) =>
    getAxiosInstance()
        .put(`/api/tasks/${uuid}`, { task: data })
        .then((res) => res.data);

// Fragments
export const getFragments = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/tasks/${uuid}/fragments`)
        .then((res) => res.data);

// Connected cases
export const getConnected = (uuid: string) =>
    getAxiosInstance()
        .get(`/api/tasks/${uuid}/connected`)
        .then((res) => res.data);
