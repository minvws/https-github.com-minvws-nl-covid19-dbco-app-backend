/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Relationship.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum RelationshipV1 {
  'VALUE_parent' = 'parent',
  'VALUE_child' = 'child',
  'VALUE_sibling' = 'sibling',
  'VALUE_partner' = 'partner',
  'VALUE_family' = 'family',
  'VALUE_roommate' = 'roommate',
  'VALUE_friend' = 'friend',
  'VALUE_student' = 'student',
  'VALUE_colleague' = 'colleague',
  'VALUE_client' = 'client',
  'VALUE_patient' = 'patient',
  'VALUE_health' = 'health',
  'VALUE_ex' = 'ex',
  'VALUE_other' = 'other',
}

/**
 *  options to be used in the forms
 */
export const relationshipV1Options = {
    [RelationshipV1.VALUE_parent]: "Ouder",
    [RelationshipV1.VALUE_child]: "Kind",
    [RelationshipV1.VALUE_sibling]: "Broer of zus",
    [RelationshipV1.VALUE_partner]: "Partner",
    [RelationshipV1.VALUE_family]: "Familielid (overig)",
    [RelationshipV1.VALUE_roommate]: "Huisgenoot",
    [RelationshipV1.VALUE_friend]: "Vriend of kennis",
    [RelationshipV1.VALUE_student]: "Medestudent of leerling",
    [RelationshipV1.VALUE_colleague]: "Collega",
    [RelationshipV1.VALUE_client]: "Klant",
    [RelationshipV1.VALUE_patient]: "Patiënt",
    [RelationshipV1.VALUE_health]: "Gezondheidszorg medewerker",
    [RelationshipV1.VALUE_ex]: "Ex-partner",
    [RelationshipV1.VALUE_other]: "Overig"
};
