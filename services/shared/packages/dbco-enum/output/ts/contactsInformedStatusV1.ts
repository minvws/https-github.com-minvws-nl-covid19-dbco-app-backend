/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContactsInformedStatus.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum ContactsInformedStatusV1 {
  'VALUE_not_started' = 'not_started',
  'VALUE_not_completed' = 'not_completed',
  'VALUE_completed_not_everyone_reached' = 'completed_not_everyone_reached',
  'VALUE_completed' = 'completed',
}

/**
 *  options to be used in the forms
 */
export const contactsInformedStatusV1Options = {
    [ContactsInformedStatusV1.VALUE_not_started]: "Nog niet gestart",
    [ContactsInformedStatusV1.VALUE_not_completed]: "Gestart, nog niet afgerond",
    [ContactsInformedStatusV1.VALUE_completed_not_everyone_reached]: "Afgerond, niet iedereen bereikt",
    [ContactsInformedStatusV1.VALUE_completed]: "Afgerond: iedereen bereikt of n.v.t."
};
