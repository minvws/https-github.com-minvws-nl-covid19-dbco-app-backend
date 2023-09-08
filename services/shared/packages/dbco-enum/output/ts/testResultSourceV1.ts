/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestResultSource.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum TestResultSourceV1 {
  'VALUE_coronit' = 'coronit',
  'VALUE_manual' = 'manual',
  'VALUE_meldportaal' = 'meldportaal',
  'VALUE_publicWebPortal' = 'publicWebPortal',
}

/**
 *  options to be used in the forms
 */
export const testResultSourceV1Options = {
    [TestResultSourceV1.VALUE_coronit]: "CoronIT",
    [TestResultSourceV1.VALUE_manual]: "Handmatig aangemaakt",
    [TestResultSourceV1.VALUE_meldportaal]: "Meldportaal",
    [TestResultSourceV1.VALUE_publicWebPortal]: "Zelfportaal"
};
