/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit informTarget.json!
 */

/**
 * Defines who should be informed. values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum InformTargetV1 {
  'VALUE_contact' = 'contact',
  'VALUE_representative' = 'representative',
}

/**
 * Defines who should be informed. options to be used in the forms
 */
export const informTargetV1Options = {
    [InformTargetV1.VALUE_contact]: "Contact",
    [InformTargetV1.VALUE_representative]: "Vertegenwoordiger"
};
