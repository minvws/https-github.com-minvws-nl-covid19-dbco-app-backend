/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit VaccinationGroup.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum VaccinationGroupV1 {
  'VALUE_resident_nursing_home' = 'resident-nursing-home',
  'VALUE_resident_institution' = 'resident-institution',
  'VALUE_healthcare_worker' = 'healthcare-worker',
  'VALUE_age_above_60' = 'age-above-60',
  'VALUE_age_below_60_medical_condition' = 'age-below-60-medical-condition',
  'VALUE_age_below_60' = 'age-below-60',
}

/**
 *  options to be used in the forms
 */
export const vaccinationGroupV1Options = {
    [VaccinationGroupV1.VALUE_resident_nursing_home]: "Bewoner verpleeghuis",
    [VaccinationGroupV1.VALUE_resident_institution]: "Bewoner instelling voor mensen met een verstandelijke beperking",
    [VaccinationGroupV1.VALUE_healthcare_worker]: "Gezondheidszorgmedewerker",
    [VaccinationGroupV1.VALUE_age_above_60]: "Ouder dan 60 jaar",
    [VaccinationGroupV1.VALUE_age_below_60_medical_condition]: "Jonger dan 60 jaar met medische indicatie",
    [VaccinationGroupV1.VALUE_age_below_60]: "Jonger dan 60 jaar zonder medische indicatie"
};
