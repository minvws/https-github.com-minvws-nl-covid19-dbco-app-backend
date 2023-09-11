/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContactTracingStatus.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum ContactTracingStatusV1 {
  'VALUE_not_approached' = 'not_approached',
  'VALUE_not_reachable' = 'not_reachable',
  'VALUE_conversation_started' = 'conversation_started',
  'VALUE_closed_outside_ggd' = 'closed_outside_ggd',
  'VALUE_closed_no_collaboration' = 'closed_no_collaboration',
  'VALUE_completed' = 'completed',
  'VALUE_new' = 'new',
  'VALUE_not_started' = 'not_started',
  'VALUE_two_times_not_reached' = 'two_times_not_reached',
  'VALUE_callback_request' = 'callback_request',
  'VALUE_loose_end' = 'loose_end',
  'VALUE_four_times_not_reached' = 'four_times_not_reached',
  'VALUE_bco_finished' = 'bco_finished',
  'VALUE_closed' = 'closed',
  'VALUE_unknown' = 'unknown',
}

/**
 *  options to be used in the forms
 */
export const contactTracingStatusV1Options = {
    [ContactTracingStatusV1.VALUE_not_approached]: "Index nog niet benaderd",
    [ContactTracingStatusV1.VALUE_not_reachable]: "De index was onbereikbaar",
    [ContactTracingStatusV1.VALUE_conversation_started]: "Gestart, nog niet afgerond",
    [ContactTracingStatusV1.VALUE_closed_outside_ggd]: "Case afronden, BCO wordt uitgevoerd buiten GGD",
    [ContactTracingStatusV1.VALUE_closed_no_collaboration]: "Case afronden, index wil niet (volledig) meewerken aan BCO",
    [ContactTracingStatusV1.VALUE_completed]: "Indexgesprek voltooid",
    [ContactTracingStatusV1.VALUE_new]: "Nieuw",
    [ContactTracingStatusV1.VALUE_not_started]: "Nog niet begonnen",
    [ContactTracingStatusV1.VALUE_two_times_not_reached]: "2x geen gehoor: index niet bereikt",
    [ContactTracingStatusV1.VALUE_callback_request]: "Terugbelverzoek",
    [ContactTracingStatusV1.VALUE_loose_end]: "Los eindje: kleine openstaande taken",
    [ContactTracingStatusV1.VALUE_four_times_not_reached]: "4x geen gehoor: index niet bereikt",
    [ContactTracingStatusV1.VALUE_bco_finished]: "BCO Afgerond",
    [ContactTracingStatusV1.VALUE_closed]: "Gesloten",
    [ContactTracingStatusV1.VALUE_unknown]: "Onbekend"
};
