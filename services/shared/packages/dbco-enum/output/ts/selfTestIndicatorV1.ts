/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit SelfTestIndicator.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum SelfTestIndicatorV1 {
  'VALUE_molecular' = 'molecular',
  'VALUE_antigen' = 'antigen',
  'VALUE_planned_retest' = 'planned-retest',
  'VALUE_no_retest' = 'no-retest',
  'VALUE_unknown' = 'unknown',
}

/**
 *  options to be used in the forms
 */
export const selfTestIndicatorV1Options = {
    [SelfTestIndicatorV1.VALUE_molecular]: "Ja, met moleculaire diagnostiek (PCR, LAMP, andere nucleïnezuur amplificatietest (NAAT))",
    [SelfTestIndicatorV1.VALUE_antigen]: "Ja, met antigeen(snel)test",
    [SelfTestIndicatorV1.VALUE_planned_retest]: "Nee, hertest volgt op een later moment ",
    [SelfTestIndicatorV1.VALUE_no_retest]: "Nee, er wordt geen hertest uitgevoerd",
    [SelfTestIndicatorV1.VALUE_unknown]: "Onbekend"
};
