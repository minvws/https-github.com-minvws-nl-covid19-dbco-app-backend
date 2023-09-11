/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CasequalityFeedback.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum CasequalityFeedbackV1 {
  'VALUE_approve_and_archive' = 'approve_and_archive',
  'VALUE_reject_and_reopen' = 'reject_and_reopen',
  'VALUE_complete' = 'complete',
  'VALUE_archive' = 'archive',
}

/**
 *  options to be used in the forms
 */
export const casequalityFeedbackV1Options = {
    [CasequalityFeedbackV1.VALUE_approve_and_archive]: "Goedgekeurd: sluiten",
    [CasequalityFeedbackV1.VALUE_reject_and_reopen]: "Aanpassing nodig: teruggeven aan werkverdeler",
    [CasequalityFeedbackV1.VALUE_complete]: "Niet gecontroleerd: teruggeven aan werkverdeler voor controle",
    [CasequalityFeedbackV1.VALUE_archive]: "Niet gecontroleerd: sluiten"
};
