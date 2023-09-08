/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit IsolationAdvice.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum IsolationAdviceV1 {
  'VALUE_live_seperated_explained' = 'live-seperated-explained',
  'VALUE_isolation_impossible_explained' = 'isolation-impossible-explained',
  'VALUE_test_advice_housemates_explained' = 'test-advice-housemates-explained',
}

/**
 *  options to be used in the forms
 */
export const isolationAdviceV1Options = {
    [IsolationAdviceV1.VALUE_live_seperated_explained]: "Gescheiden leven van huisgenoten & schoonmaken van gedeelde keuken en/of badkamer uitgelegd",
    [IsolationAdviceV1.VALUE_isolation_impossible_explained]: "Quarantainebeleid wanneer strikte thuisisolatie niet mogelijk is uitgelegd (de laatste dag van quarantaine is afhankelijk van de dag dat index uit isolatie gaat)",
    [IsolationAdviceV1.VALUE_test_advice_housemates_explained]: "Testadvies voor nauwe contacten & huisgenoten uitgelegd"
};
