/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit RiskLocationType.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum RiskLocationTypeV1 {
  'VALUE_nursing_home' = 'nursing-home',
  'VALUE_disabled_residental_car' = 'disabled-residental-car',
  'VALUE_ggz_institution' = 'ggz-institution',
  'VALUE_assisted_living' = 'assisted-living',
  'VALUE_prison' = 'prison',
  'VALUE_youth_institution' = 'youth-institution',
  'VALUE_asylum_center' = 'asylum-center',
  'VALUE_social_living' = 'social-living',
  'VALUE_other' = 'other',
}

/**
 *  options to be used in the forms
 */
export const riskLocationTypeV1Options = {
    [RiskLocationTypeV1.VALUE_nursing_home]: "Verpleeghuis of woonzorgcentrum",
    [RiskLocationTypeV1.VALUE_disabled_residental_car]: "Instelling voor verstandelijk en/of lichamelijk beperkten",
    [RiskLocationTypeV1.VALUE_ggz_institution]: "GGZ-instelling (geestelijke gezondheidszorg)",
    [RiskLocationTypeV1.VALUE_assisted_living]: "Begeleid kleinschalig wonen",
    [RiskLocationTypeV1.VALUE_prison]: "Penitentiaire instelling",
    [RiskLocationTypeV1.VALUE_youth_institution]: "(Semi-)residentiele jeugdinstelling",
    [RiskLocationTypeV1.VALUE_asylum_center]: "Asielzoekerscentrum / opvang voor vluchtelingen",
    [RiskLocationTypeV1.VALUE_social_living]: "Overige maatschappelijke opvang",
    [RiskLocationTypeV1.VALUE_other]: "Anders"
};
