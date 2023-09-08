/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestReason.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum TestReasonV2 {
  'VALUE_symptoms' = 'symptoms',
  'VALUE_contact_warned_by_ggd' = 'contact_warned_by_ggd',
  'VALUE_contact' = 'contact',
  'VALUE_outbreak' = 'outbreak',
  'VALUE_return' = 'return',
  'VALUE_work' = 'work',
  'VALUE_education_daycare' = 'education_daycare',
  'VALUE_medical_treatment' = 'medical_treatment',
  'VALUE_event' = 'event',
  'VALUE_meeting_people' = 'meeting_people',
  'VALUE_regular_selftest' = 'regular_selftest',
  'VALUE_other' = 'other',
}

/**
 *  options to be used in the forms
 */
export const testReasonV2Options = {
    [TestReasonV2.VALUE_symptoms]: "Klachten",
    [TestReasonV2.VALUE_contact_warned_by_ggd]: "Gewaarschuwd door GGD na contact met besmet persoon",
    [TestReasonV2.VALUE_contact]: "Gewaarschuwd door een besmet persoon vanwege contact",
    [TestReasonV2.VALUE_outbreak]: "Betrokkenheid bij uitbraak (bijv. school / werk / instelling / club)",
    [TestReasonV2.VALUE_return]: "Na terugkeer uit risicogebied (vanaf niveau oranje)",
    [TestReasonV2.VALUE_work]: "Voor werk",
    [TestReasonV2.VALUE_education_daycare]: "Voor onderwijs / kinderopvang",
    [TestReasonV2.VALUE_medical_treatment]: "Voor medische behandeling",
    [TestReasonV2.VALUE_event]: "Voor/na evenement (bijv. Fieldlab of testen voor toegang)",
    [TestReasonV2.VALUE_meeting_people]: "Na ontmoeting met mensen",
    [TestReasonV2.VALUE_regular_selftest]: "Voor de zekerheid af en toe een zelftest",
    [TestReasonV2.VALUE_other]: "Andere reden"
};
