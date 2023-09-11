/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit LabTestIndicator.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum LabTestIndicatorV1 {
  'VALUE_molecular' = 'molecular',
  'VALUE_antigen' = 'antigen',
  'VALUE_unknown' = 'unknown',
  'VALUE_other' = 'other',
}

/**
 *  options to be used in the forms
 */
export const labTestIndicatorV1Options = {
    [LabTestIndicatorV1.VALUE_molecular]: "Moleculaire diagnostiek (PCR, LAMP, andere nucleinezuur-amplificatietest (NAAT)) ",
    [LabTestIndicatorV1.VALUE_antigen]: "Antigeen(snel)test ",
    [LabTestIndicatorV1.VALUE_unknown]: "Onbekend",
    [LabTestIndicatorV1.VALUE_other]: "Anders, namelijk"
};
