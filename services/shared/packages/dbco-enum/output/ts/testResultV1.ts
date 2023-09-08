/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestResult.json!
 */

/**
 * Test result values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum TestResultV1 {
  'VALUE_positive' = 'positive',
  'VALUE_negative' = 'negative',
  'VALUE_unknown' = 'unknown',
}

/**
 * Test result options to be used in the forms
 */
export const testResultV1Options = {
    [TestResultV1.VALUE_positive]: "Positief",
    [TestResultV1.VALUE_negative]: "Negatief",
    [TestResultV1.VALUE_unknown]: "Onbekend"
};
