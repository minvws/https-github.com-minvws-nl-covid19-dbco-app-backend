import type { MessageStatusV1, MessageTemplateTypeV1 } from '@dbco/enum';

export interface MessageSummary {
    uuid: string;
    mailVariant: MessageTemplateTypeV1;
    caseUuid: string;
    taskUuid: string | null;
    toEmail: string;
    toName: string;
    telephone: string | null;
    subject: string;
    createdAt: string;
    expiresAt: string | null;
    notificationSentAt: string | null;
    status: MessageStatusV1;
    isExpired: boolean;
    isDeleted: boolean;
    identityRequired: boolean;
    isIdentified: boolean;
    hasAttachments?: boolean;
}
export interface Message {
    uuid: string;
    mailVariant: MessageTemplateTypeV1;
    caseUuid: string;
    taskUuid: string | null;
    toEmail: string;
    toName: string;
    telephone: string | null;
    subject: string;
    text: string;
    createdAt: string;
    notificationSentAt: string | null;
    expiresAt: string | null;
    status: MessageStatusV1;
    isExpired: boolean;
    isDeleted: boolean;
    isSecure: boolean;
    identityRequired: boolean;
    isIdentified: boolean;
    hasAttachments?: boolean;
    attachments?: Attachment[];
}

interface Attachment {
    uuid: string;
    fileName: string;
    createdAt: string;
    updatedAt: string;
    inactiveSince: string;
}
