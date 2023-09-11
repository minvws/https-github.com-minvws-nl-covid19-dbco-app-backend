/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit InformStatus.json!
 */

/**
 * Status for informing a contact values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum InformStatusV1 {
  'VALUE_uninformed' = 'uninformed',
  'VALUE_unreachable' = 'unreachable',
  'VALUE_emailSent' = 'emailSent',
  'VALUE_informed' = 'informed',
}

/**
 * Status for informing a contact options to be used in the forms
 */
export const informStatusV1Options = {
    [InformStatusV1.VALUE_uninformed]: "Nog niet geïnformeerd",
    [InformStatusV1.VALUE_unreachable]: "Geen gehoor",
    [InformStatusV1.VALUE_emailSent]: "Alleen gemaild",
    [InformStatusV1.VALUE_informed]: "Geïnformeerd"
};
