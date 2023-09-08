/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit BCOStatus.json!
 */

/**
 * BCO status for case values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum BcoStatusV1 {
  'VALUE_draft' = 'draft',
  'VALUE_open' = 'open',
  'VALUE_completed' = 'completed',
  'VALUE_archived' = 'archived',
  'VALUE_unknown' = 'unknown',
}

/**
 * BCO status for case options to be used in the forms
 */
export const bcoStatusV1Options = {
    [BcoStatusV1.VALUE_draft]: "Concept",
    [BcoStatusV1.VALUE_open]: "Open",
    [BcoStatusV1.VALUE_completed]: "Controleren",
    [BcoStatusV1.VALUE_archived]: "Gesloten",
    [BcoStatusV1.VALUE_unknown]: "Onbekend"
};
