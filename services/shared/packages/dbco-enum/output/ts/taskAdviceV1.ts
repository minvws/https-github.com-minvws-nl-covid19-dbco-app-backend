/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TaskAdvice.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum TaskAdviceV1 {
  'VALUE_quarantine_not_applicable' = 'quarantine-not-applicable',
  'VALUE_quarantine_explained' = 'quarantine-explained',
  'VALUE_live_seperated_explained' = 'live-seperated-explained',
  'VALUE_do_test_asap' = 'do-test-asap',
  'VALUE_do_test_when_symptoms' = 'do-test-when-symptoms',
}

/**
 *  options to be used in the forms
 */
export const taskAdviceV1Options = {
    [TaskAdviceV1.VALUE_quarantine_not_applicable]: "Quarantaine niet van toepassing",
    [TaskAdviceV1.VALUE_quarantine_explained]: "Uitleg quarantainebeleid als strikte thuisisolatie niet mogelijk is",
    [TaskAdviceV1.VALUE_live_seperated_explained]: "Uitleg gescheiden leven van huisgenoten & schoonmaken van gedeelde keuken en/of badkamer",
    [TaskAdviceV1.VALUE_do_test_asap]: "Doe zo snel mogelijk een coronatest",
    [TaskAdviceV1.VALUE_do_test_when_symptoms]: "Laat je testen wanneer klachten ontstaan"
};
