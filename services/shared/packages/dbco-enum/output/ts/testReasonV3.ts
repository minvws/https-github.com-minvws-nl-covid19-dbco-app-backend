/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestReason.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum TestReasonV3 {
  'VALUE_symptoms' = 'symptoms',
  'VALUE_contact_warned_by_ggd' = 'contact_warned_by_ggd',
  'VALUE_contact' = 'contact',
  'VALUE_outbreak' = 'outbreak',
  'VALUE_return' = 'return',
  'VALUE_work' = 'work',
  'VALUE_education_daycare' = 'education_daycare',
  'VALUE_medical_treatment' = 'medical_treatment',
  'VALUE_meeting_many_people' = 'meeting_many_people',
  'VALUE_proof_of_recovery' = 'proof_of_recovery',
  'VALUE_confirm_selftest' = 'confirm_selftest',
  'VALUE_other' = 'other',
}

/**
 *  options to be used in the forms
 */
export const testReasonV3Options = {
    [TestReasonV3.VALUE_symptoms]: "Klachten",
    [TestReasonV3.VALUE_contact_warned_by_ggd]: "Gewaarschuwd door GGD na contact met besmet persoon",
    [TestReasonV3.VALUE_contact]: "Gewaarschuwd door een besmet persoon vanwege contact",
    [TestReasonV3.VALUE_outbreak]: "Betrokkenheid bij uitbraak (bijv. school / werk / instelling / club)",
    [TestReasonV3.VALUE_return]: "Na terugkeer uit risicogebied (vanaf niveau oranje)",
    [TestReasonV3.VALUE_work]: "Voor werk",
    [TestReasonV3.VALUE_education_daycare]: "Voor onderwijs / kinderopvang",
    [TestReasonV3.VALUE_medical_treatment]: "Voor medische behandeling",
    [TestReasonV3.VALUE_meeting_many_people]: "Na ontmoeting met (veel) mensen",
    [TestReasonV3.VALUE_proof_of_recovery]: "Voor een herstelbewijs",
    [TestReasonV3.VALUE_confirm_selftest]: "Wens tot bevestigen zelftest",
    [TestReasonV3.VALUE_other]: "Andere reden"
};
