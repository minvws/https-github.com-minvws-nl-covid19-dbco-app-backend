/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit MessageTemplateType.json!
 */

/**
 * Message template type values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum MessageTemplateTypeV1 {
  'VALUE_deletedMessage' = 'deletedMessage',
  'VALUE_personalAdvice' = 'personalAdvice',
  'VALUE_contactInfection' = 'contactInfection',
  'VALUE_missedPhone' = 'missedPhone',
}

/**
 * Message template type options to be used in the forms
 */
export const messageTemplateTypeV1Options = {
    [MessageTemplateTypeV1.VALUE_deletedMessage]: "Bericht verwijderd",
    [MessageTemplateTypeV1.VALUE_personalAdvice]: "Informatiebrief versturen",
    [MessageTemplateTypeV1.VALUE_contactInfection]: "Informatiebrief versturen naar contact",
    [MessageTemplateTypeV1.VALUE_missedPhone]: "Verstuur een e-mail als telefonisch contact niet lukt"
};
