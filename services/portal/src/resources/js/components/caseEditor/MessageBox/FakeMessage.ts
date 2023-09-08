import { fakerjs } from '@/utils/test';
import { MessageStatusV1, MessageTemplateTypeV1 } from '@dbco/enum';
import type { MessageSummary } from '@dbco/portal-api/message.dto';

export const caseUuid = fakerjs.string.uuid();

export const generateMessageSummary = (taskUuid: string | null = null): MessageSummary => ({
    uuid: fakerjs.string.uuid(),
    mailVariant: MessageTemplateTypeV1.VALUE_personalAdvice,
    caseUuid: caseUuid,
    taskUuid,
    toEmail: fakerjs.internet.email(),
    toName: fakerjs.person.firstName(),
    telephone: fakerjs.phone.number(),
    subject: fakerjs.lorem.sentence(),
    createdAt: fakerjs.date.past().toString(),
    expiresAt: fakerjs.date.soon().toString(),
    notificationSentAt: fakerjs.date.recent().toString(),
    status: MessageStatusV1.VALUE_draft,
    isExpired: false,
    isDeleted: false,
    identityRequired: false,
    isIdentified: false,
    hasAttachments: false,
});
export const defaultMessages = [generateMessageSummary()];
