import type { ExpertQuestionTypeV1 } from '@dbco/enum';
import type { User, SupervisionRoles } from './user';
import type { PaginatedRequestOptions } from './pagination';

export interface ExpertQuestionRequest {
    type: ExpertQuestionTypeV1;
    phone: string;
    subject: string;
    question: string;
}

export interface ExpertQuestionResponseAnswer {
    createdAt: string;
    value: string;
    answeredBy: User | null;
}

export interface ExpertQuestionResponse {
    caseUuid: string;
    createdAt: string;
    assignedUser: User | null;
    phone: string;
    question: string;
    subject: string;
    type: ExpertQuestionTypeV1;
    updatedAt: string;
    user: User;
    uuid: string;
    answer: ExpertQuestionResponseAnswer | null;
    caseOrganisationName: string | null;
}

export interface ExpertQuestionRequestOptions extends PaginatedRequestOptions {
    type?: SupervisionRoles;
}
