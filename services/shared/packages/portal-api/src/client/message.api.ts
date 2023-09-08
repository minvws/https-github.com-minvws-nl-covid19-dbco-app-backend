import { getAxiosInstance } from '../defaults';
import type { MessageTemplateTypeV1 } from '@dbco/enum';
import type { RenderedMailTemplate } from '../mail.dto';
import type { MessageSummary, Message } from '../message.dto';

export const getMessages = (uuid: string): Promise<{ messages: MessageSummary[] }> =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/messages`)
        .then((res) => res.data);

export const getMessage = (caseUuid: string, uuid: string): Promise<Message> =>
    getAxiosInstance()
        .get(`/api/cases/${caseUuid}/messages/${uuid}`)
        .then((res) => res.data);

export const getEmailTemplateForCaseUuid = (uuid: string, type: MessageTemplateTypeV1): Promise<RenderedMailTemplate> =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/messages/template/${type}`)
        .then((res) => res.data);

export const getEmailTemplateForContactUuid = (
    uuid: string,
    type: MessageTemplateTypeV1,
    contactUuid: string
): Promise<RenderedMailTemplate> =>
    getAxiosInstance()
        .get(`/api/cases/${uuid}/messages/template/${type}/${contactUuid}`)
        .then((res) => res.data);

export const sendMessageToCase = (
    uuid: string,
    type: MessageTemplateTypeV1,
    attachments: string[],
    addedText?: string
): Promise<Message> =>
    getAxiosInstance()
        .post(`/api/cases/${uuid}/messages`, {
            type,
            attachments,
            addedText,
        })
        .then((res) => res.data);

export const sendMessageToContact = (
    uuid: string,
    type: MessageTemplateTypeV1,
    contactUuid: string,
    attachments: string[],
    addedText?: string
): Promise<Message> =>
    getAxiosInstance()
        .post(`/api/cases/${uuid}/messages/${contactUuid}`, {
            type,
            attachments,
            addedText,
        })
        .then((res) => res.data);
