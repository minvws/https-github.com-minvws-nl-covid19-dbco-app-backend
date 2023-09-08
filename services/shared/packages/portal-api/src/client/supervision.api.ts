import { getAxiosInstance } from '../defaults';
import type {
    ExpertQuestionRequestOptions,
    ExpertQuestionRequest,
    ExpertQuestionResponse,
} from '@dbco/portal-api/supervision.dto';
import type { PaginatedResponse } from '@dbco/portal-api/pagination';
import type { CancelToken } from 'axios';
import type { SupervisionRoles } from '@dbco/portal-api/user';

export const askQuestion = (uuid: string, question: ExpertQuestionRequest): Promise<ExpertQuestionResponse> =>
    getAxiosInstance()
        .post(`/api/case/${uuid}/expertQuestion`, question)
        .then((res) => res.data);

export const dropQuestion = (uuid: string): Promise<ExpertQuestionResponse> =>
    getAxiosInstance()
        .delete(`api/expert-questions/${uuid}/assignment`)
        .then((res) => res.data);

export const findQuestionByCaseId = (caseId: string, role: SupervisionRoles): Promise<ExpertQuestionResponse> =>
    getAxiosInstance()
        .post(`api/expert-questions/find-by-case-id`, { case_id: caseId, expert_question_type: role })
        .then((res) => res.data);

export const getQuestion = (uuid: string): Promise<ExpertQuestionResponse> =>
    getAxiosInstance()
        .get(`api/expert-questions/${uuid}`)
        .then((res) => res.data);

export const getQuestions = (
    options: ExpertQuestionRequestOptions,
    cancelToken?: CancelToken
): Promise<PaginatedResponse<ExpertQuestionResponse>> => {
    const { page, perPage, order, sort, type } = options;
    return getAxiosInstance()
        .get(`api/expert-questions`, {
            params: {
                page,
                perPage,
                order,
                sort,
                type,
            },
            cancelToken,
        })
        .then((res) => res.data);
};

export const pickupQuestion = (uuid: string, assignedUserUuid: string): Promise<ExpertQuestionResponse> =>
    getAxiosInstance()
        .post(`api/expert-questions/${uuid}/assignment`, { assigned_user_uuid: assignedUserUuid })
        .then((res) => res.data);

export const answerQuestion = (uuid: string, answer: string): Promise<ExpertQuestionResponse> =>
    getAxiosInstance()
        .put(`api/expert-questions/${uuid}/answer`, { answer })
        .then((res) => res.data);
