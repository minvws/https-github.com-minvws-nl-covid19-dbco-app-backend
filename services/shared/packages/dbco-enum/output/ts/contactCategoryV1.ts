/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContactCategory.json!
 */

/**
 * Contact categories. values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum ContactCategoryV1 {
  'VALUE_1' = '1',
  'VALUE_2a' = '2a',
  'VALUE_2b' = '2b',
  'VALUE_3a' = '3a',
  'VALUE_3b' = '3b',
}

/**
 * Contact categories. options to be used in the forms
 */
export const contactCategoryV1Options = {
    [ContactCategoryV1.VALUE_1]: "1 - Huisgenoot (Leven in dezelfde woonomgeving en langdurig contact op minder dan 1,5 meter)",
    [ContactCategoryV1.VALUE_2a]: "2A - Nauw contact (Opgeteld meer dan 15 minuten binnen 1,5 meter)",
    [ContactCategoryV1.VALUE_2b]: "2B - Nauw contact (Opgeteld minder dan 15 minuten binnen 1,5 meter, met hoogrisico contact)",
    [ContactCategoryV1.VALUE_3a]: "3A - Overig contact (Opgeteld meer dan 15 minuten op meer dan 1,5 meter, in dezelfde ruimte)",
    [ContactCategoryV1.VALUE_3b]: "3B - Overig contact (Opgeteld minder dan 15 minuten binnen 1,5 meter, zonder hoogrisico contact, in dezelfde ruimte of buiten)"
};
