/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CaseNoteType.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum CaseNoteTypeV1 {
  'VALUE_case_added' = 'case-added',
  'VALUE_case_returned' = 'case-returned',
  'VALUE_case_checked_approved_closed' = 'case-checked-approved-closed',
  'VALUE_case_checked_rejected_returned' = 'case-checked-rejected-returned',
  'VALUE_case_not_checked_returned' = 'case-not-checked-returned',
  'VALUE_case_not_checked_closed' = 'case-not-checked-closed',
  'VALUE_case_directly_archived' = 'case-directly-archived',
  'VALUE_case_reopened' = 'case-reopened',
  'VALUE_case_changed_organisation' = 'case-changed-organisation',
  'VALUE_case_note' = 'case-note',
  'VALUE_case_note_index_by_search' = 'case-note-index-by-search',
  'VALUE_case_note_contact_by_search' = 'case-note-contact-by-search',
}

/**
 *  options to be used in the forms
 */
export const caseNoteTypeV1Options = {
    [CaseNoteTypeV1.VALUE_case_added]: "Notitie toegevoegd bij aanmaken",
    [CaseNoteTypeV1.VALUE_case_returned]: "Case teruggegeven aan werkverdeler",
    [CaseNoteTypeV1.VALUE_case_checked_approved_closed]: "Case gecontroleerd, goedgekeurd en case gesloten",
    [CaseNoteTypeV1.VALUE_case_checked_rejected_returned]: "Case gecontroleerd, afgekeurd en teruggegeven",
    [CaseNoteTypeV1.VALUE_case_not_checked_returned]: "Case niet gecontroleerd en teruggegeven voor controle",
    [CaseNoteTypeV1.VALUE_case_not_checked_closed]: "Case niet gecontroleerd en case gesloten",
    [CaseNoteTypeV1.VALUE_case_directly_archived]: "Case direct gesloten",
    [CaseNoteTypeV1.VALUE_case_reopened]: "Case heropend",
    [CaseNoteTypeV1.VALUE_case_changed_organisation]: "Case is overgedragen van organisatie",
    [CaseNoteTypeV1.VALUE_case_note]: "Notitie door werkverdeler",
    [CaseNoteTypeV1.VALUE_case_note_index_by_search]: "Notitie over index via dossier zoeken",
    [CaseNoteTypeV1.VALUE_case_note_contact_by_search]: "Notitie over contact via dossier zoeken"
};
