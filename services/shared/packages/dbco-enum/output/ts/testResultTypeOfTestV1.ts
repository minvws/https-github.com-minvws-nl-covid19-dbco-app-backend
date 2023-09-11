/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestResultTypeOfTest.json!
 */

/**
 * Type of test result values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum TestResultTypeOfTestV1 {
  'VALUE_pcr' = 'pcr',
  'VALUE_antigen' = 'antigen',
  'VALUE_selftest' = 'selftest',
  'VALUE_custom' = 'custom',
  'VALUE_unknown' = 'unknown',
}

/**
 * Type of test result options to be used in the forms
 */
export const testResultTypeOfTestV1Options = [
    {
        "label": "Moleculaire diagnostiek (PCR, LAMP, andere nucleinezuuramplificatietest (NAAT))",
        "value": TestResultTypeOfTestV1.VALUE_pcr,
        "type": "lab"
    },
    {
        "label": "Antigeen (door zorgprofessional)",
        "value": TestResultTypeOfTestV1.VALUE_antigen,
        "type": "lab"
    },
    {
        "label": "Antigeen (zelftest)",
        "value": TestResultTypeOfTestV1.VALUE_selftest,
        "type": "selftest"
    },
    {
        "label": "Anders",
        "value": TestResultTypeOfTestV1.VALUE_custom,
        "type": "unknown"
    },
    {
        "label": "Onbekend",
        "value": TestResultTypeOfTestV1.VALUE_unknown,
        "type": "unknown"
    }
];
