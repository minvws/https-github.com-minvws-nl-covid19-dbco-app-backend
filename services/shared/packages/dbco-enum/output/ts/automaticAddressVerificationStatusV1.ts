/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit AutomaticAddressVerificationStatus.json!
 */

/**
 * Verification status of address details, automatically executed by the system during index identification. values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum AutomaticAddressVerificationStatusV1 {
  'VALUE_unchecked' = 'unchecked',
  'VALUE_verified' = 'verified',
  'VALUE_unverified' = 'unverified',
}

/**
 * Verification status of address details, automatically executed by the system during index identification. options to be used in the forms
 */
export const automaticAddressVerificationStatusV1Options = {
    [AutomaticAddressVerificationStatusV1.VALUE_unchecked]: "Niet gecontroleerd",
    [AutomaticAddressVerificationStatusV1.VALUE_verified]: "Geverifieerd",
    [AutomaticAddressVerificationStatusV1.VALUE_unverified]: "Ongeverifieerd"
};
