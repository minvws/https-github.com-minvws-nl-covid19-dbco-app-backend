/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PreviousInfectionReason.json!
 */

/**
 * Reasons of the previous infection suspicion values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum PreviousInfectionReasonV1 {
  'VALUE_positive' = 'positive',
  'VALUE_contact' = 'contact',
}

/**
 * Reasons of the previous infection suspicion options to be used in the forms
 */
export const previousInfectionReasonV1Options = {
    [PreviousInfectionReasonV1.VALUE_positive]: "Bewezen met een positieve testuitslag",
    [PreviousInfectionReasonV1.VALUE_contact]: "Gevolg van categorie 1 of 2 contact met iemand die positief getest is"
};
