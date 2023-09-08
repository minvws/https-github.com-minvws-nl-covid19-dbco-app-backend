/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextRelationship.json!
 */

/**
 * Context relationship values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum ContextRelationshipV1 {
  'VALUE_staff' = 'staff',
  'VALUE_visitor' = 'visitor',
  'VALUE_resident' = 'resident',
  'VALUE_patient' = 'patient',
  'VALUE_teacher' = 'teacher',
  'VALUE_student' = 'student',
  'VALUE_traveler' = 'traveler',
  'VALUE_unknown' = 'unknown',
  'VALUE_other' = 'other',
}

/**
 * Context relationship options to be used in the forms
 */
export const contextRelationshipV1Options = {
    [ContextRelationshipV1.VALUE_staff]: "Medewerker",
    [ContextRelationshipV1.VALUE_visitor]: "Bezoeker",
    [ContextRelationshipV1.VALUE_resident]: "Bewoner",
    [ContextRelationshipV1.VALUE_patient]: "Patiënt",
    [ContextRelationshipV1.VALUE_teacher]: "Docent",
    [ContextRelationshipV1.VALUE_student]: "Student / Leerling",
    [ContextRelationshipV1.VALUE_traveler]: "Reiziger/passagier",
    [ContextRelationshipV1.VALUE_unknown]: "Onbekend",
    [ContextRelationshipV1.VALUE_other]: "Anders"
};
