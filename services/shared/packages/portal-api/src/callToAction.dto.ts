import type { User } from './user';
import type { PaginatedRequestOptions } from './pagination';
import type { CallToActionEventV1 } from '@dbco/enum';

export interface CallToActionHistoryItems {
    events: CallToActionHistoryItem[];
    deletedAt: string | null;
}

export interface CallToActionHistoryItem {
    datetime: string;
    callToActionEvent: CallToActionEventV1;
    note: string | null;
    user: User;
}

export enum CallToActionSortOptions {
    CREATED_AT = 'createdAt',
    EXPIRES_AT = 'expiresAt',
    STATUS = 'status',
}

export interface CallToActionTable extends PaginatedRequestOptions<CallToActionSortOptions> {
    infiniteId: number;
}

export interface CallToActionResponse {
    assignedUserUuid: string | null;
    createdAt: string;
    expiresAt: string;
    resource: {
        type: string;
        uuid: string;
    };
    subject: string;
    uuid: string;
    createdBy?: User | null;
    description?: string;
}

export interface CallToActionRequest {
    subject: string;
    organisation_uuid: string;
    resource_uuid: string;
    resource_type: string;
    resource_permission: string;
    expires_at: string;
    description: string;
    role: string;
}
