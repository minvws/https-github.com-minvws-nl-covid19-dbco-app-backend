export interface RenderedMailTemplate {
    subject: string;
    isSecure: boolean;
    body: string;
    footer: string;
    language: string;
    attachments?: MailTemplateAttachment[];
}
export interface MailTemplateAttachment {
    uuid: string;
    filename: string;
}
