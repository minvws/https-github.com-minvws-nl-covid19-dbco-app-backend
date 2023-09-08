/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit IndexStatus.json!
 */

/**
 * Index status for case values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum IndexStatusV1 {
  'VALUE_initial' = 'initial',
  'VALUE_pairing_request_accepted' = 'pairing_request_accepted',
  'VALUE_paired' = 'paired',
  'VALUE_delivered' = 'delivered',
  'VALUE_timeout' = 'timeout',
  'VALUE_expired' = 'expired',
}

/**
 * Index status for case options to be used in the forms
 */
export const indexStatusV1Options = {
    [IndexStatusV1.VALUE_initial]: "Koppelcode gedeeld",
    [IndexStatusV1.VALUE_pairing_request_accepted]: "Pairing request geaccepteerd",
    [IndexStatusV1.VALUE_paired]: "Nog niets ontvangen",
    [IndexStatusV1.VALUE_delivered]: "Gegevens aangeleverd",
    [IndexStatusV1.VALUE_timeout]: "Verlopen",
    [IndexStatusV1.VALUE_expired]: "Koppelcode verlopen"
};
