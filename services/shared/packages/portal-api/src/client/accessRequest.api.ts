import { getAxiosInstance } from '../defaults';

export const getCaseFragments = (caseUuid: string) =>
    getAxiosInstance()
        .get(`/api/access-requests/case/${caseUuid}/fragments`)
        .then((res) => res.data);
export const getOverview = () =>
    getAxiosInstance()
        .get('/api/access-requests/overview')
        .then((res) => res.data);
export const getTaskFragments = (taskUuid: string) =>
    getAxiosInstance()
        .get(`/api/access-requests/task/${taskUuid}/fragments`)
        .then((res) => res.data);

// Case
export const deleteCase = (caseUuid: string) =>
    getAxiosInstance()
        .delete(`/api/access-requests/case/${caseUuid}`)
        .then((res) => res.data);
export const downloadCase = (caseUuid: string, downloadCompleteToken: string, type?: string) =>
    getAxiosInstance()
        .get(`/api/access-requests/case/${caseUuid}/download${type === 'html' ? '/html' : ''}`, {
            params: {
                downloadCompleteToken,
            },
            responseType: 'blob',
        })
        .then((res) => {
            const fileName = res.headers['content-disposition']?.match(/filename="(.+)"/);
            return {
                file: res.data,
                fileName: fileName ? fileName[1] : '',
            };
        });
export const restoreCase = (caseUuid: string) =>
    getAxiosInstance().post(`/api/access-requests/case/${caseUuid}/restore`);

// Task
export const deleteTask = (taskUuid: string) =>
    getAxiosInstance()
        .delete(`/api/access-requests/task/${taskUuid}`)
        .then((res) => res.data);
export const downloadTask = (taskUuid: string, downloadCompleteToken: string, type?: string) =>
    getAxiosInstance()
        .get(`/api/access-requests/task/${taskUuid}/download${type === 'html' ? '/html' : ''}`, {
            params: {
                downloadCompleteToken,
            },
            responseType: 'blob',
        })
        .then((res) => {
            const fileName = res.headers['content-disposition']?.match(/filename="(.+)"/);
            return {
                file: res.data,
                fileName: fileName ? fileName[1] : '',
            };
        });
export const restoreTask = (taskUuid: string) =>
    getAxiosInstance().post(`/api/access-requests/task/${taskUuid}/restore`);
