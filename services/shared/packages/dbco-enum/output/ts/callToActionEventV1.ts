/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CallToActionEvent.json!
 */

/**
 * CallToAction events values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum CallToActionEventV1 {
  'VALUE_picked_up' = 'picked-up',
  'VALUE_returned' = 'returned',
  'VALUE_note' = 'note',
  'VALUE_completed' = 'completed',
  'VALUE_expired' = 'expired',
}

/**
 * CallToAction events options to be used in the forms
 */
export const callToActionEventV1Options = {
    [CallToActionEventV1.VALUE_picked_up]: "Opgepakt",
    [CallToActionEventV1.VALUE_returned]: "Teruggegeven",
    [CallToActionEventV1.VALUE_note]: "Notitie geplaatst",
    [CallToActionEventV1.VALUE_completed]: "Afgerond",
    [CallToActionEventV1.VALUE_expired]: "Verlopen"
};
