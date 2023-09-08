/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit IndexRiskProfile.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum IndexRiskProfileV1 {
  'VALUE_hospital_admitted' = 'hospital_admitted',
  'VALUE_is_immuno_compromised' = 'is_immuno_compromised',
  'VALUE_has_symptoms' = 'has_symptoms',
  'VALUE_no_symptoms' = 'no_symptoms',
}

/**
 *  options to be used in the forms
 */
export const indexRiskProfileV1Options = {
    [IndexRiskProfileV1.VALUE_hospital_admitted]: "Ziekenhuisopname",
    [IndexRiskProfileV1.VALUE_is_immuno_compromised]: "Verminderde afweer",
    [IndexRiskProfileV1.VALUE_has_symptoms]: "Symptomatische index standaard",
    [IndexRiskProfileV1.VALUE_no_symptoms]: "Asymptomatische index standaard"
};
