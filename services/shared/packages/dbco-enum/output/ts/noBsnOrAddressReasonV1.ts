/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit NoBsnOrAddressReason.json!
 */

/**
 * Reason for missing bsn values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum NoBsnOrAddressReasonV1 {
  'VALUE_homeless' = 'homeless',
  'VALUE_foreign_passerby' = 'foreign_passerby',
  'VALUE_postalcode_unknown' = 'postalcode_unknown',
  'VALUE_no_cooperation' = 'no_cooperation',
}

/**
 * Reason for missing bsn options to be used in the forms
 */
export const noBsnOrAddressReasonV1Options = {
    [NoBsnOrAddressReasonV1.VALUE_homeless]: "Dak- of thuisloos",
    [NoBsnOrAddressReasonV1.VALUE_foreign_passerby]: "Buitenlandse passant (geen BSN)",
    [NoBsnOrAddressReasonV1.VALUE_postalcode_unknown]: "Postcode onbekend",
    [NoBsnOrAddressReasonV1.VALUE_no_cooperation]: "Wil niet meewerken"
};
