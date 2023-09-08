/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit HospitalReason.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum HospitalReasonV1 {
  'VALUE_covid' = 'covid',
  'VALUE_other' = 'other',
  'VALUE_unknown' = 'unknown',
}

/**
 *  options to be used in the forms
 */
export const hospitalReasonV1Options = {
    [HospitalReasonV1.VALUE_covid]: "(Verdenking van) COVID-19",
    [HospitalReasonV1.VALUE_other]: "Andere indicatie",
    [HospitalReasonV1.VALUE_unknown]: "Onbekend"
};
